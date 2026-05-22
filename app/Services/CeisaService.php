<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CeisaService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $apiKey;
    protected $idPerusahaan;

    public function __construct()
    {
        $this->baseUrl      = config('ceisa.base_url_dev');
        $this->username     = config('ceisa.username_dev');
        $this->password     = config('ceisa.password_dev');
        $this->apiKey       = config('ceisa.api_key_dev');
        $this->idPerusahaan = config('ceisa.id_perusahaan_dev');
    }

    /**
     * Dapatkan token, opsional untuk memaksa refresh token baru.
     */
    public function getToken($forceRefresh = false)
    {
        if ($forceRefresh) {
            Cache::forget('ceisa_access_token');
        }

        return Cache::remember('ceisa_access_token', 3500, function () {
            $response = Http::post("{$this->baseUrl}/nle-oauth/v1/user/login", [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                return $response->json()['item']['access_token'];
            }

            throw new \Exception('Gagal mendapatkan token: ' . $response->body());
        });
    }

    /**
     * Helper Method: Mengirim request HTTP dengan auto-retry jika token kedaluwarsa.
     */
    protected function requestWithRetry($method, $url, $options = [], $retry = true)
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization'    => 'Bearer ' . $token,
            'Beacukai-Api-Key' => $this->apiKey,
            'Content-Type'     => 'application/json',
            'Accept'           => 'application/json',
        ])->send($method, $url, $options);

        $body = $response->json();

        // Deteksi jika server CEISA merespon token invalid/expired (HTTP 401 ATAU response json berisi error)
        $isInvalidToken = $response->status() === 401 || (isset($body['error']) && $body['error'] === 'invalid_token');

        if ($isInvalidToken && $retry) {
            Log::warning('CEISA Token expired/invalid. Mencoba refresh token dan mengulangi request...');

            // Hapus token lama dari cache dan ambil token baru
            $this->getToken(true);

            // Jalankan ulang request dengan token yang baru (set parameter retry = false untuk cegah loop tanpa henti)
            return $this->requestWithRetry($method, $url, $options, false);
        }

        return $response;
    }

    public function cekStatus()
    {
        $response = $this->requestWithRetry('GET', "{$this->baseUrl}/openapi/status", [
            'query' => ['idPerusahaan' => $this->idPerusahaan]
        ]);

        return $response->json();
    }

    public function cekKurs($kurs)
    {
        $response = $this->requestWithRetry('GET', "{$this->baseUrl}/openapi/kurs/{$kurs}");

        return $response->json();
    }

    public function kirimDokumen($payload, $isFinal = 'false')
    {
        $response = $this->requestWithRetry('POST', "{$this->baseUrl}/openapi/document?isFinal={$isFinal}", [
            'json' => $payload
        ]);

        return [
            'status_code' => $response->status(),
            'body'        => $response->json(),
            'successful'  => $response->successful()
        ];
    }

    public function getStatusDraft($nomorAju)
    {
        try {
            $response = $this->requestWithRetry('GET', "{$this->baseUrl}/v1/temp/{$nomorAju}");

            return $response->json();
        } catch (\Exception $e) {
            Log::error('CEISA API Get Status Error: ' . $e->getMessage());
            return null;
        }
    }
}
