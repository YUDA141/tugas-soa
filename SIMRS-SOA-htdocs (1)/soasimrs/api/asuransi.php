<?php
require_once __DIR__ . "/../config/db.php";
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $res = $conn->query("SELECT * FROM asuransi ORDER BY id DESC");
    respond(true, "Daftar asuransi", $res->fetch_all(MYSQLI_ASSOC));
}

$in = getJsonInput();

if ($method === 'POST') {
    $kode = trim($in['kode_asuransi'] ?? '');
    $nama = trim($in['nama_asuransi'] ?? '');
    $jenis = in_array($in['jenis'] ?? '', ['BPJS', 'Swasta', 'Perusahaan']) ? $in['jenis'] : 'Swasta';
    $persen = max(0, min(100, (int)($in['persen_tanggungan'] ?? 80)));
    $kontak = trim($in['kontak'] ?? '');
    if ($kode === '' || $nama === '') respond(false, "Kode dan nama asuransi wajib diisi", null, 400);

    $stmt = $conn->prepare("INSERT INTO asuransi (kode_asuransi, nama_asuransi, jenis, persen_tanggungan, kontak) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $kode, $nama, $jenis, $persen, $kontak);
    if ($stmt->execute()) {
        respond(true, "Asuransi berhasil ditambahkan", ["id" => $conn->insert_id], 201);
    }
    respond(false, "Gagal menambah asuransi: " . $conn->error, null, 400);
}

if ($method === 'PUT') {
    $id = (int)($in['id'] ?? $_GET['id'] ?? 0);
    $kode = trim($in['kode_asuransi'] ?? '');
    $nama = trim($in['nama_asuransi'] ?? '');
    $jenis = in_array($in['jenis'] ?? '', ['BPJS', 'Swasta', 'Perusahaan']) ? $in['jenis'] : 'Swasta';
    $persen = max(0, min(100, (int)($in['persen_tanggungan'] ?? 80)));
    $kontak = trim($in['kontak'] ?? '');
    if ($id <= 0 || $kode === '' || $nama === '') respond(false, "Data asuransi tidak valid", null, 400);

    $stmt = $conn->prepare("UPDATE asuransi SET kode_asuransi=?, nama_asuransi=?, jenis=?, persen_tanggungan=?, kontak=? WHERE id=?");
    $stmt->bind_param("sssisi", $kode, $nama, $jenis, $persen, $kontak, $id);
    if ($stmt->execute()) {
        respond(true, "Asuransi berhasil diperbarui", ["id" => $id]);
    }
    respond(false, "Gagal memperbarui asuransi: " . $conn->error, null, 400);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? $in['id'] ?? 0);
    if ($id <= 0) respond(false, "ID asuransi tidak valid", null, 400);
    $stmt = $conn->prepare("DELETE FROM asuransi WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        respond(true, "Asuransi berhasil dihapus", ["id" => $id]);
    }
    respond(false, "Gagal menghapus asuransi: " . $conn->error, null, 400);
}

respond(false, "Metode tidak didukung", null, 405);
