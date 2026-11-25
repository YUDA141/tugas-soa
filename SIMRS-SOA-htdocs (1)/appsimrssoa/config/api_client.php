<?php
// Mengarah langsung ke folder Web Service di htdocs (Server B)
// Jika Server B dipindah ke komputer/IP lain, cukup ganti localhost dengan IP Server B
define("API_BASE_URL", "http://localhost/soasimrs/api");

function callApi($endpoint, $method = 'GET', $payload = null) {
    $url = rtrim(API_BASE_URL, '/') . '/' . ltrim($endpoint, '/');
    $ch = curl_init();

    $headers = [
        "Content-Type: application/json",
        "Accept: application/json"
    ];

    $upper = strtoupper($method);
    if ($upper === 'GET' && is_array($payload) && !empty($payload)) {
        $sep = str_contains($url, '?') ? '&' : '?';
        $url .= $sep . http_build_query($payload);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($upper === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload ?? []));
    } elseif (in_array($upper, ['PUT', 'DELETE'])) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $upper);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
    }

    $raw = curl_exec($ch);
    $errNo = curl_errno($ch);
    $errMsg = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errNo !== 0) {
        return [
            "status" => false,
            "message" => "Gagal terhubung ke layanan data ({$errMsg}). Pastikan folder soasimrs ada di htdocs.",
            "data" => null,
            "http_code" => $httpCode
        ];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [
            "status" => false,
            "message" => "Respons layanan data tidak valid",
            "data" => null,
            "http_code" => $httpCode
        ];
    }
    $decoded["http_code"] = $httpCode;
    return $decoded;
}

function formatRupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}
