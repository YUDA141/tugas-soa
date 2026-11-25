<?php
require_once __DIR__ . "/../config/db.php";
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!empty($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT d.*, p.nama_poli, u.username FROM dokter d LEFT JOIN poliklinik p ON d.poliklinik_id = p.id LEFT JOIN pengguna u ON d.pengguna_id = u.id WHERE d.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        respond((bool)$row, $row ? "Data dokter ditemukan" : "Data tidak ditemukan", $row, $row ? 200 : 404);
    }
    $sql = "SELECT d.*, p.nama_poli, u.username FROM dokter d LEFT JOIN poliklinik p ON d.poliklinik_id = p.id LEFT JOIN pengguna u ON d.pengguna_id = u.id ORDER BY d.id DESC";
    $res = $conn->query($sql);
    respond(true, "Daftar dokter", $res->fetch_all(MYSQLI_ASSOC));
}

$in = getJsonInput();

if ($method === 'POST') {
    $penggunaId = !empty($in['pengguna_id']) ? (int)$in['pengguna_id'] : null;
    $poliId = (int)($in['poliklinik_id'] ?? 0);
    $nipStr = trim($in['nip_str'] ?? '');
    $nama = trim($in['nama_dokter'] ?? '');
    $spesialis = trim($in['spesialisasi'] ?? '');
    $hp = trim($in['no_hp'] ?? '');
    $tarif = (float)($in['tarif_konsultasi'] ?? 100000);
    $jadwal = trim($in['jadwal_praktik'] ?? 'Senin - Jumat (08:00 - 14:00)');

    if ($poliId <= 0 || $nipStr === '' || $nama === '' || $spesialis === '') {
        respond(false, "Poliklinik, NIP/STR, nama dokter, dan spesialisasi wajib diisi", null, 400);
    }

    $stmt = $conn->prepare("INSERT INTO dokter (pengguna_id, poliklinik_id, nip_str, nama_dokter, spesialisasi, no_hp, tarif_konsultasi, jadwal_praktik) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssds", $penggunaId, $poliId, $nipStr, $nama, $spesialis, $hp, $tarif, $jadwal);
    if ($stmt->execute()) {
        respond(true, "Data dokter berhasil disimpan", ["id" => $conn->insert_id], 201);
    }
    respond(false, "Gagal menyimpan dokter: " . $conn->error, null, 400);
}

if ($method === 'PUT') {
    $id = (int)($in['id'] ?? $_GET['id'] ?? 0);
    $penggunaId = !empty($in['pengguna_id']) ? (int)$in['pengguna_id'] : null;
    $poliId = (int)($in['poliklinik_id'] ?? 0);
    $nipStr = trim($in['nip_str'] ?? '');
    $nama = trim($in['nama_dokter'] ?? '');
    $spesialis = trim($in['spesialisasi'] ?? '');
    $hp = trim($in['no_hp'] ?? '');
    $tarif = (float)($in['tarif_konsultasi'] ?? 100000);
    $jadwal = trim($in['jadwal_praktik'] ?? '');

    if ($id <= 0 || $poliId <= 0 || $nipStr === '' || $nama === '') {
        respond(false, "Data update dokter tidak lengkap", null, 400);
    }

    $stmt = $conn->prepare("UPDATE dokter SET pengguna_id=?, poliklinik_id=?, nip_str=?, nama_dokter=?, spesialisasi=?, no_hp=?, tarif_konsultasi=?, jadwal_praktik=? WHERE id=?");
    $stmt->bind_param("iissssdsi", $penggunaId, $poliId, $nipStr, $nama, $spesialis, $hp, $tarif, $jadwal, $id);
    if ($stmt->execute()) {
        respond(true, "Data dokter berhasil diperbarui", ["id" => $id]);
    }
    respond(false, "Gagal memperbarui dokter: " . $conn->error, null, 400);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? $in['id'] ?? 0);
    if ($id <= 0) respond(false, "ID dokter tidak valid", null, 400);
    $stmt = $conn->prepare("DELETE FROM dokter WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        respond(true, "Data dokter berhasil dihapus", ["id" => $id]);
    }
    respond(false, "Gagal menghapus dokter (masih terkait kunjungan)", null, 400);
}

respond(false, "Metode tidak didukung", null, 405);
