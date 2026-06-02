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
    protected $idPlatform;

    public function __construct()
    {
        $env = config('ceisa.env', 'dev');

        $this->baseUrl      = config("ceisa.base_url_{$env}");
        $this->username     = config("ceisa.username_{$env}");
        $this->password     = config("ceisa.password_{$env}");
        $this->apiKey       = config("ceisa.api_key_{$env}");
        $this->idPerusahaan = config("ceisa.id_perusahaan_{$env}");
        $this->idPlatform   = config("ceisa.id_platform_{$env}");
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
            $response = Http::withoutVerifying()->post("{$this->baseUrl}/nle-oauth/v1/user/login", [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                return $response->json()['item']['access_token'];
            }

            throw new \Exception('Gagal mendapatkan token: ' . $response->body());
        });
    }

    protected function requestWithRetry($method, $url, $data = [], $retry = true)
    {
        $token = $this->getToken();


        $headers = [
            'Authorization'    => 'Bearer ' . $token,
            'Beacukai-Api-Key' => $this->apiKey,
            'Content-Type'     => 'application/json',
            'Accept'           => 'application/json',
        ];



        $timeout = (strtoupper($method) === 'GET') ? 3 : 15;
        $request = Http::timeout($timeout)->withoutVerifying()->withHeaders($headers);

        // GET: $data dikirim sebagai query string
        // POST/PUT/PATCH: $data dikirim sebagai JSON body
        if (strtoupper($method) === 'GET') {
            $response = $request->get($url, $data);
        } else {
            $response = $request->withBody(json_encode($data), 'application/json')
                                ->send($method, $url);
        }

        $body = $response->json();

        // Deteksi jika server CEISA merespon token invalid/expired
        $isInvalidToken = $response->status() === 401
            || (isset($body['error']) && $body['error'] === 'invalid_token');

        if ($isInvalidToken && $retry) {
            Log::warning('CEISA Token expired/invalid. Mencoba refresh token dan mengulangi request...');
            $this->getToken(true);
            return $this->requestWithRetry($method, $url, $data, false);
        }

        return $response;
    }

    /**
     * Cek status koneksi ke CEISA
     */
    public function cekStatus()
    {
        $response = $this->requestWithRetry('GET', "{$this->baseUrl}/openapi/status", [
            'idPerusahaan' => $this->idPerusahaan
        ]);

        return $response->json();
    }

    /**
     * Cek kurs
     */
    public function cekKurs($kurs)
    {
        $response = $this->requestWithRetry('GET', "{$this->baseUrl}/openapi/kurs/{$kurs}");

        return $response->json();
    }

    /**
     * Kirim dokumen ke CEISA.
     */
    public function kirimDokumen($payload, $isFinal = 'false')
    {
        $response = $this->requestWithRetry(
            'POST',
            "{$this->baseUrl}/openapi/document?isFinal={$isFinal}",
            $payload
        );

        return [
            'status_code' => $response->status(),
            'body'        => $response->json(),
            'successful'  => $response->successful()
        ];
    }

    /**
     * Ambil status/riwayat dokumen berdasarkan nomor aju (26 digit).
     */
    public function getStatusDraft($noAju)
    {
        try {
            $response = $this->requestWithRetry(
                'GET',
                "{$this->baseUrl}/openapi/status/{$noAju}"
            );

            Log::info('CEISA getStatusDraft response', [
                'http_status' => $response->status(),
                'body'        => $response->json()
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('CEISA API Get Status Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
