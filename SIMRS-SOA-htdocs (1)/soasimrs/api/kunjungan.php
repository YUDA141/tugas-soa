<?php
require_once __DIR__ . "/../config/db.php";
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $where = [];
    if (!empty($_GET['pasien_id'])) {
        $where[] = "k.pasien_id = " . (int)$_GET['pasien_id'];
    }
    if (!empty($_GET['dokter_id'])) {
        $where[] = "k.dokter_id = " . (int)$_GET['dokter_id'];
    }
    if (!empty($_GET['status']) && in_array($_GET['status'], ['menunggu', 'diperiksa', 'selesai', 'batal'])) {
        $st = $conn->real_escape_string($_GET['status']);
        $where[] = "k.status = '{$st}'";
    }
    $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

    $sql = "SELECT k.*, ps.no_rm, ps.nama_pasien, ps.pengguna_id AS pasien_pengguna_id,
                   d.nama_dokter, d.spesialisasi, d.pengguna_id AS dokter_pengguna_id,
                   p.nama_poli, a.nama_asuransi, a.persen_tanggungan
            FROM kunjungan k
            JOIN pasien ps ON k.pasien_id = ps.id
            JOIN dokter d ON k.dokter_id = d.id
            JOIN poliklinik p ON k.poliklinik_id = p.id
            LEFT JOIN asuransi a ON k.asuransi_id = a.id
            {$sqlWhere}
            ORDER BY k.id DESC";
    $res = $conn->query($sql);
    respond(true, "Daftar kunjungan pasien", $res->fetch_all(MYSQLI_ASSOC));
}

$in = getJsonInput();

if ($method === 'POST') {
    $pasienId = (int)($in['pasien_id'] ?? 0);
    $dokterId = (int)($in['dokter_id'] ?? 0);
    $tgl = trim($in['tanggal_kunjungan'] ?? date('Y-m-d'));
    $keluhan = trim($in['keluhan'] ?? '');

    if ($pasienId <= 0 || $dokterId <= 0 || $keluhan === '') {
        respond(false, "Pasien, dokter, dan keluhan wajib diisi", null, 400);
    }

    $qd = $conn->prepare("SELECT poliklinik_id, tarif_konsultasi FROM dokter WHERE id = ?");
    $qd->bind_param("i", $dokterId);
    $qd->execute();
    $dok = $qd->get_result()->fetch_assoc();
    if (!$dok) respond(false, "Dokter tidak ditemukan", null, 404);

    $qp = $conn->prepare("SELECT ps.asuransi_id, a.persen_tanggungan FROM pasien ps LEFT JOIN asuransi a ON ps.asuransi_id = a.id WHERE ps.id = ?");
    $qp->bind_param("i", $pasienId);
    $qp->execute();
    $pas = $qp->get_result()->fetch_assoc();
    if (!$pas) respond(false, "Pasien tidak ditemukan", null, 404);

    $poliId = (int)$dok['poliklinik_id'];
    $biayaTotal = (float)$dok['tarif_konsultasi'];
    $asuransiId = !empty($pas['asuransi_id']) ? (int)$pas['asuransi_id'] : null;
    $persen = $asuransiId ? (float)($pas['persen_tanggungan'] ?? 0) : 0.0;
    $biayaDitanggung = round(($biayaTotal * $persen) / 100, 2);
    $biayaPasien = round($biayaTotal - $biayaDitanggung, 2);
    $noKunjungan = "KNJ-" . date("Ymd") . "-" . rand(100, 999);

    $stmt = $conn->prepare("INSERT INTO kunjungan (no_kunjungan, pasien_id, dokter_id, poliklinik_id, asuransi_id, tanggal_kunjungan, keluhan, biaya_total, biaya_ditanggung, biaya_pasien, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu')");
    $stmt->bind_param("siiiissddd", $noKunjungan, $pasienId, $dokterId, $poliId, $asuransiId, $tgl, $keluhan, $biayaTotal, $biayaDitanggung, $biayaPasien);
    if ($stmt->execute()) {
        respond(true, "Pendaftaran kunjungan berhasil", ["id" => $conn->insert_id, "no_kunjungan" => $noKunjungan], 201);
    }
    respond(false, "Gagal mendaftarkan kunjungan: " . $conn->error, null, 400);
}

if ($method === 'PUT') {
    $id = (int)($in['id'] ?? $_GET['id'] ?? 0);
    $diagnosa = trim($in['diagnosa'] ?? '');
    $status = in_array($in['status'] ?? '', ['menunggu', 'diperiksa', 'selesai', 'batal']) ? $in['status'] : 'diperiksa';
    if ($id <= 0) respond(false, "ID kunjungan tidak valid", null, 400);

    $stmt = $conn->prepare("UPDATE kunjungan SET diagnosa=?, status=? WHERE id=?");
    $stmt->bind_param("ssi", $diagnosa, $status, $id);
    if ($stmt->execute()) {
        respond(true, "Status & diagnosa kunjungan diperbarui", ["id" => $id]);
    }
    respond(false, "Gagal memperbarui kunjungan: " . $conn->error, null, 400);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? $in['id'] ?? 0);
    if ($id <= 0) respond(false, "ID kunjungan tidak valid", null, 400);
    $stmt = $conn->prepare("DELETE FROM kunjungan WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        respond(true, "Data kunjungan dihapus", ["id" => $id]);
    }
    respond(false, "Gagal menghapus kunjungan: " . $conn->error, null, 400);
}

respond(false, "Metode tidak didukung", null, 405);
