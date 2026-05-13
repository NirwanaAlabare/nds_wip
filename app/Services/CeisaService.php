<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CeisaService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $apiKey;
    protected $idPerusahaan;

    public function __construct()
    {
        $this->baseUrl      = config('ceisa.base_url');
        $this->username     = config('ceisa.username');
        $this->password     = config('ceisa.password');
        $this->apiKey       = config('ceisa.api_key');
        $this->idPerusahaan = config('ceisa.id_perusahaan');
    }

    public function getToken()
    {
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

    public function cekStatus()
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization'    => 'Bearer ' . $token,
            'Beacukai-Api-Key' => $this->apiKey,
            'content-type'     => 'application/json',
        ])->get("{$this->baseUrl}/openapi/status", [
            'idPerusahaan' => $this->idPerusahaan
        ]);

        return $response->json();
    }

    public function cekKurs($kurs)
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization'    => 'Bearer ' . $token,
            'Beacukai-Api-Key' => $this->apiKey,
            'content-type'     => 'application/json',
        ])->get("{$this->baseUrl}/openapi/kurs/{$kurs}");

        return $response->json();
    }

    public function kirimDokumen($payload, $isFinal = 'false')
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization'    => 'Bearer ' . $token,
            'Beacukai-Api-Key' => $this->apiKey,
            'Content-Type'     => 'application/json',
        ])->post("{$this->baseUrl}/openapi/document?isFinal={$isFinal}", $payload);

        return [
            'status_code' => $response->status(),
            'body'        => $response->json(),
            'successful'  => $response->successful()
        ];
    }
}
