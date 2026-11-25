<?php
require_once __DIR__ . "/../config/db.php";

$tglAwal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tglAkhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

$stmtK = $conn->prepare("SELECT k.*, ps.no_rm, ps.nama_pasien, d.nama_dokter, p.nama_poli,
                               COALESCE(a.nama_asuransi, 'Umum / Mandiri') AS nama_asuransi
                        FROM kunjungan k
                        JOIN pasien ps ON k.pasien_id = ps.id
                        JOIN dokter d ON k.dokter_id = d.id
                        JOIN poliklinik p ON k.poliklinik_id = p.id
                        LEFT JOIN asuransi a ON k.asuransi_id = a.id
                        WHERE k.tanggal_kunjungan BETWEEN ? AND ?
                        ORDER BY k.tanggal_kunjungan DESC, k.id DESC");
$stmtK->bind_param("ss", $tglAwal, $tglAkhir);
$stmtK->execute();
$rows = $stmtK->get_result()->fetch_all(MYSQLI_ASSOC);

$totalKunjungan = count($rows);
$totalBiaya = array_sum(array_map(fn($r) => (float)$r['biaya_total'], $rows));
$totalDitanggung = array_sum(array_map(fn($r) => (float)$r['biaya_ditanggung'], $rows));
$totalPasien = array_sum(array_map(fn($r) => (float)$r['biaya_pasien'], $rows));

$rekapPoli = [];
foreach ($rows as $r) {
    $poli = $r['nama_poli'];
    if (!isset($rekapPoli[$poli])) {
        $rekapPoli[$poli] = ["nama_poli" => $poli, "jumlah" => 0, "total_biaya" => 0];
    }
    $rekapPoli[$poli]["jumlah"]++;
    $rekapPoli[$poli]["total_biaya"] += (float)$r['biaya_total'];
}

$rekapAsuransi = [];
foreach ($rows as $r) {
    $asr = $r['nama_asuransi'];
    if (!isset($rekapAsuransi[$asr])) {
        $rekapAsuransi[$asr] = ["nama_asuransi" => $asr, "jumlah" => 0, "ditanggung" => 0];
    }
    $rekapAsuransi[$asr]["jumlah"]++;
    $rekapAsuransi[$asr]["ditanggung"] += (float)$r['biaya_ditanggung'];
}

respond(true, "Laporan SIMRS berhasil dimuat", [
    "periode" => ["tgl_awal" => $tglAwal, "tgl_akhir" => $tglAkhir],
    "ringkasan" => [
        "total_kunjungan" => $totalKunjungan,
        "total_pasien_terdaftar" => (int)$conn->query("SELECT COUNT(*) AS c FROM pasien")->fetch_assoc()['c'],
        "total_dokter" => (int)$conn->query("SELECT COUNT(*) AS c FROM dokter")->fetch_assoc()['c'],
        "total_poliklinik" => (int)$conn->query("SELECT COUNT(*) AS c FROM poliklinik")->fetch_assoc()['c'],
        "total_biaya" => $totalBiaya,
        "total_ditanggung" => $totalDitanggung,
        "total_pasien" => $totalPasien
    ],
    "rekap_poliklinik" => array_values($rekapPoli),
    "rekap_asuransi" => array_values($rekapAsuransi),
    "detail_kunjungan" => $rows
]);
