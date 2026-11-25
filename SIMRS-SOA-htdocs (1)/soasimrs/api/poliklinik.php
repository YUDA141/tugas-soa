<?php
require_once __DIR__ . "/../config/db.php";
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (!empty($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM poliklinik WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        respond((bool)$row, $row ? "Data poliklinik ditemukan" : "Data tidak ditemukan", $row, $row ? 200 : 404);
    }
    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
        $like = "%{$q}%";
        $stmt = $conn->prepare("SELECT * FROM poliklinik WHERE kode_poli LIKE ? OR nama_poli LIKE ? ORDER BY id DESC");
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        respond(true, "Daftar poliklinik terfilter", $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    }
    $res = $conn->query("SELECT * FROM poliklinik ORDER BY id DESC");
    respond(true, "Daftar seluruh poliklinik", $res->fetch_all(MYSQLI_ASSOC));
}

$in = getJsonInput();

if ($method === 'POST') {
    $kode = trim($in['kode_poli'] ?? '');
    $nama = trim($in['nama_poli'] ?? '');
    $lokasi = trim($in['lokasi_lantai'] ?? 'Lantai 1');
    $desk = trim($in['deskripsi'] ?? '');
    if ($kode === '' || $nama === '') respond(false, "Kode dan nama poliklinik wajib diisi", null, 400);

    $stmt = $conn->prepare("INSERT INTO poliklinik (kode_poli, nama_poli, lokasi_lantai, deskripsi) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $kode, $nama, $lokasi, $desk);
    if ($stmt->execute()) {
        respond(true, "Poliklinik berhasil ditambahkan", ["id" => $conn->insert_id], 201);
    }
    respond(false, "Gagal menambah poliklinik: " . $conn->error, null, 400);
}

if ($method === 'PUT') {
    $id = (int)($in['id'] ?? $_GET['id'] ?? 0);
    $kode = trim($in['kode_poli'] ?? '');
    $nama = trim($in['nama_poli'] ?? '');
    $lokasi = trim($in['lokasi_lantai'] ?? 'Lantai 1');
    $desk = trim($in['deskripsi'] ?? '');
    if ($id <= 0 || $kode === '' || $nama === '') respond(false, "ID, kode, dan nama poliklinik wajib diisi", null, 400);

    $stmt = $conn->prepare("UPDATE poliklinik SET kode_poli=?, nama_poli=?, lokasi_lantai=?, deskripsi=? WHERE id=?");
    $stmt->bind_param("ssssi", $kode, $nama, $lokasi, $desk, $id);
    if ($stmt->execute()) {
        respond(true, "Poliklinik berhasil diperbarui", ["id" => $id]);
    }
    respond(false, "Gagal memperbarui poliklinik: " . $conn->error, null, 400);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? $in['id'] ?? 0);
    if ($id <= 0) respond(false, "ID poliklinik tidak valid", null, 400);
    $stmt = $conn->prepare("DELETE FROM poliklinik WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        respond(true, "Poliklinik berhasil dihapus", ["id" => $id]);
    }
    respond(false, "Gagal menghapus poliklinik (masih digunakan dokter/kunjungan)", null, 400);
}

respond(false, "Metode tidak didukung", null, 405);
