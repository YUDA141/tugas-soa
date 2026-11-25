<?php
require_once __DIR__ . "/../config/db.php";
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!empty($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT ps.*, a.nama_asuransi, a.persen_tanggungan, u.username FROM pasien ps LEFT JOIN asuransi a ON ps.asuransi_id = a.id LEFT JOIN pengguna u ON ps.pengguna_id = u.id WHERE ps.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        respond((bool)$row, $row ? "Data pasien ditemukan" : "Data tidak ditemukan", $row, $row ? 200 : 404);
    }
    $sql = "SELECT ps.*, a.nama_asuransi, a.persen_tanggungan, u.username FROM pasien ps LEFT JOIN asuransi a ON ps.asuransi_id = a.id LEFT JOIN pengguna u ON ps.pengguna_id = u.id ORDER BY ps.id DESC";
    $res = $conn->query($sql);
    respond(true, "Daftar pasien", $res->fetch_all(MYSQLI_ASSOC));
}

$in = getJsonInput();

if ($method === 'POST') {
    $penggunaId = !empty($in['pengguna_id']) ? (int)$in['pengguna_id'] : null;
    $asuransiId = !empty($in['asuransi_id']) ? (int)$in['asuransi_id'] : null;
    $noRm = trim($in['no_rm'] ?? ('RM-' . date('YmdHis')));
    $nik = trim($in['nik'] ?? '');
    $noPolis = trim($in['no_polis'] ?? '');
    $nama = trim($in['nama_pasien'] ?? '');
    $jk = ($in['jenis_kelamin'] ?? 'L') === 'P' ? 'P' : 'L';
    $tglLahir = trim($in['tanggal_lahir'] ?? '2000-01-01');
    $hp = trim($in['no_hp'] ?? '');
    $alamat = trim($in['alamat'] ?? '');

    if ($nik === '' || $nama === '') {
        respond(false, "NIK dan nama pasien wajib diisi", null, 400);
    }

    $stmt = $conn->prepare("INSERT INTO pasien (pengguna_id, asuransi_id, no_rm, nik, no_polis, nama_pasien, jenis_kelamin, tanggal_lahir, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssssss", $penggunaId, $asuransiId, $noRm, $nik, $noPolis, $nama, $jk, $tglLahir, $hp, $alamat);
    if ($stmt->execute()) {
        respond(true, "Data pasien berhasil disimpan", ["id" => $conn->insert_id, "no_rm" => $noRm], 201);
    }
    respond(false, "Gagal menyimpan pasien: " . $conn->error, null, 400);
}

if ($method === 'PUT') {
    $id = (int)($in['id'] ?? $_GET['id'] ?? 0);
    $penggunaId = !empty($in['pengguna_id']) ? (int)$in['pengguna_id'] : null;
    $asuransiId = !empty($in['asuransi_id']) ? (int)$in['asuransi_id'] : null;
    $noRm = trim($in['no_rm'] ?? '');
    $nik = trim($in['nik'] ?? '');
    $noPolis = trim($in['no_polis'] ?? '');
    $nama = trim($in['nama_pasien'] ?? '');
    $jk = ($in['jenis_kelamin'] ?? 'L') === 'P' ? 'P' : 'L';
    $tglLahir = trim($in['tanggal_lahir'] ?? '2000-01-01');
    $hp = trim($in['no_hp'] ?? '');
    $alamat = trim($in['alamat'] ?? '');

    if ($id <= 0 || $nik === '' || $nama === '') respond(false, "Data update pasien tidak valid", null, 400);

    $stmt = $conn->prepare("UPDATE pasien SET pengguna_id=?, asuransi_id=?, no_rm=?, nik=?, no_polis=?, nama_pasien=?, jenis_kelamin=?, tanggal_lahir=?, no_hp=?, alamat=? WHERE id=?");
    $stmt->bind_param("iissssssssi", $penggunaId, $asuransiId, $noRm, $nik, $noPolis, $nama, $jk, $tglLahir, $hp, $alamat, $id);
    if ($stmt->execute()) {
        respond(true, "Data pasien berhasil diperbarui", ["id" => $id]);
    }
    respond(false, "Gagal memperbarui pasien: " . $conn->error, null, 400);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? $in['id'] ?? 0);
    if ($id <= 0) respond(false, "ID pasien tidak valid", null, 400);
    $stmt = $conn->prepare("DELETE FROM pasien WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        respond(true, "Data pasien berhasil dihapus", ["id" => $id]);
    }
    respond(false, "Gagal menghapus pasien: " . $conn->error, null, 400);
}

respond(false, "Metode tidak didukung", null, 405);
