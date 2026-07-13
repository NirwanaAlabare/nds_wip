<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CeisaService
{
    protected $currentEnv;
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $apiKey;
    protected $idPerusahaan;
    protected $idPlatform;

    public function __construct()
    {
        $env = config('ceisa.env', 'dev');
        $this->setEnv($env);
    }

    /**
     * Set environment secara dinamis (live / dev)
     */
    public function setEnv($env)
    {
        $this->currentEnv   = $env;
        $this->baseUrl      = config("ceisa.base_url_{$env}");
        $this->username     = config("ceisa.username_{$env}");
        $this->password     = config("ceisa.password_{$env}");
        $this->apiKey       = config("ceisa.api_key_{$env}");
        $this->idPerusahaan = config("ceisa.id_perusahaan_{$env}");
        $this->idPlatform   = config("ceisa.id_platform_{$env}");

        return $this;
    }


    public function useUserCredential()
    {
        if (!auth()->check()) {
            throw new \Exception('Gagal: Anda belum login.');
        }

        $userCred = \Illuminate\Support\Facades\DB::connection('mysql_sb')
            ->table('master_ceisa_credentials')
            ->where('username', auth()->user()->username)
            ->first();

        if (!$userCred) {
            throw new \Exception('Gagal: Anda belum memiliki akses API CEISA. Silakan untuk membuat akun kredential CEISA.');
        }

        $this->username = $userCred->ceisa_username;
        $this->password = $userCred->ceisa_password;
        $this->apiKey   = $userCred->ceisa_api_key;

        return $this;
    }

    protected function detectEnv($payload)
    {
        $kodeDokumen = null;

        if (is_array($payload)) {
            $kodeDokumen = $payload['kodeDokumen'] ?? ($payload['header']['kodeDokumen'] ?? null);
        } elseif (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (is_array($decoded)) {
                $kodeDokumen = $decoded['kodeDokumen'] ?? ($decoded['header']['kodeDokumen'] ?? null);
            }
        }

        $kodeDokumen = (string) $kodeDokumen;

        if (in_array($kodeDokumen, ['23', '40'])) {
            $this->setEnv('live');
        } else {
            $this->setEnv('dev');
        }
    }

    public function getToken($forceRefresh = false)
    {
        $cacheKey = "ceisa_access_token_{$this->currentEnv}";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 3500, function () {
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
            Log::warning("CEISA Token expired/invalid untuk env {$this->currentEnv}. Mencoba refresh token dan mengulangi request...");
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
        $this->useUserCredential();
        $this->setEnv('live');
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
        $this->useUserCredential();
        $this->setEnv('live');

        if (is_array($payload) && !empty($this->idPlatform)) {
            $payload['idPlatform'] = $this->idPlatform;
        }

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

    public function getStatusDraft($noAju)
    {
        try {
            $response = $this->requestWithRetry(
                'GET',
                "{$this->baseUrl}/openapi/status/{$noAju}"
            );

            Log::info("CEISA getStatusDraft response ({$this->currentEnv})", [
                'http_status' => $response->status(),
                'body'        => $response->json()
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error("CEISA API Get Status Error ({$this->currentEnv}): " . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Kirim dokumen BC 2.5 ke CEISA.
     */
    public function kirimDokumenBc25($payload, $isFinal = 'false')
    {
        $this->useUserCredential();
        $this->setEnv('dev');

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
     * Kirim dokumen BC 2.3 ke CEISA.
     */
    public function kirimDokumenBc23($payload, $isFinal = 'false')
    {
        $this->useUserCredential();
        $this->setEnv('dev');

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
     * Kirim dokumen BC 2.7 ke CEISA.
     */
    public function kirimDokumenBc27($payload, $isFinal = 'false')
    {
        $this->useUserCredential();
        $this->setEnv('dev');

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
     * Kirim dokumen BC 3.0 ke CEISA
     */
    public function kirimDokumenBc30($payload, $isFinal = 'false')
    {
        $this->useUserCredential();
        $this->setEnv('dev');

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

    // kirim dokumen BC 3.3 ke CEISA
    public function kirimDokumenBc33($payload, $isFinal = 'false')
    {
        $this->useUserCredential();
        $this->setEnv('dev');

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

    // kirim dokumen BC 2.6.1 ke CEISA
    public function kirimDokumenBc261($payload, $isFinal = 'false')
    {
        $this->useUserCredential();
        $this->setEnv('dev');

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

    // kirim dokumen BC 2.6.2 ke CEISA
    public function kirimDokumenBc262($payload, $isFinal = 'false')
    {
        $this->useUserCredential();
        $this->setEnv('dev');

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

    // kirim dokumen BC 4.1 ke CEISA
    public function kirimDokumenBc41($payload, $isFinal = 'false')
    {
        $this->useUserCredential();
        $this->setEnv('dev');

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
     * Delete draft dokumen dari CEISA
     */
    public function deleteDraft($nomorAju)
    {
        $this->useUserCredential();
        $response = $this->requestWithRetry(
            'DELETE',
            "{$this->baseUrl}/openapi/status/{$nomorAju}"
        );

        return [
            'status_code' => $response->status(),
            'body'        => $response->json(),
            'successful'  => $response->successful()
        ];
    }

    function getPelabuhan($kata)
    {
        $response = $this->requestWithRetry(
            'GET',
            "{$this->baseUrl}/openapi/pelabuhan/kata/{$kata}"
        );

        return $response->json();
    }

    function getTps($kata)
    {
        // Jika input berupa 6 digit angka (kode kantor pabean, misal 050500 untuk Bandung),
        // gunakan endpoint /openapi/tps/{kodeKantor}
        if (is_numeric($kata) && strlen($kata) === 6) {
            $url = "{$this->baseUrl}/openapi/tps/{$kata}";
        } else {
            $url = "{$this->baseUrl}/openapi/tps/kata/{$kata}";
        }

        $response = $this->requestWithRetry(
            'GET',
            $url
        );

        return $response->json();
    }

    /**
     * Tarik detail lengkap dokumen dari CEISA berdasarkan jenis dokumen dan nomor aju.
     * Sangat berguna untuk sinkronisasi data dari Portal CEISA ke lokal (mengatasi tabrakan no aju).
     * Contoh jenisDokumen: '27', '23', '30', '33'
     */
    public function getDocumentDetail($jenisDokumen, $nomorAju)
    {
        $this->useUserCredential();

        if (in_array((string)$jenisDokumen, ['23', '40'])) {
            $this->setEnv('live');
        } else {
            $this->setEnv('dev');
        }

        $response = $this->requestWithRetry(
            'GET',
            "{$this->baseUrl}/openapi/document/detail/{$jenisDokumen}/{$nomorAju}"
        );

        return [
            'status_code' => $response->status(),
            'body'        => $response->json(),
            'successful'  => $response->successful()
        ];
    }

    /**
     * Tarik riwayat status dan respon dokumen dari CEISA berdasarkan nomor aju.
     */
    public function getStatus($nomorAju)
    {
        $this->useUserCredential();

        $response = $this->requestWithRetry(
            'GET',
            "{$this->baseUrl}/openapi/status/{$nomorAju}"
        );

        return [
            'status_code' => $response->status(),
            'body'        => $response->json(),
            'successful'  => $response->successful()
        ];
    }

    /**
     * Mengambil sequence terakhir (6 digit) dari server CEISA berdasarkan prefix Nomor Aju.
     * Mencegah tabrakan nomor aju antara lokal dan portal CEISA.
     */
    public function getLastSequenceFromCeisa($prefix, $jenisBc)
    {
        try {
            $this->useUserCredential();
            if (in_array((string)$jenisBc, ['23', '40', '2.3', '4.0'])) {
                $this->setEnv('live');
            } else {
                $this->setEnv('dev');
            }

            $response = $this->requestWithRetry('GET', "{$this->baseUrl}/openapi/status", [
                'idPerusahaan' => $this->idPerusahaan
            ]);

            $body = $response->json();
            $maxSeq = 0;

            if (isset($body['dataStatus']) && is_array($body['dataStatus'])) {
                foreach ($body['dataStatus'] as $item) {
                    $noAju = $item['nomorAju'] ?? '';
                    if (str_starts_with($noAju, $prefix) && strlen($noAju) === 26) {
                        $seq = (int) substr($noAju, -6);
                        if ($seq > $maxSeq) {
                            $maxSeq = $seq;
                        }
                    }
                }
            }

            return $maxSeq;
        } catch (\Exception $e) {
            Log::error("Error getLastSequenceFromCeisa: " . $e->getMessage());
            return 0;
        }
    }
}
