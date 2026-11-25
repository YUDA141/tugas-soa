<?php
require_once __DIR__ . "/../config/db.php";
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $res = $conn->query("SELECT id, username, nama_lengkap, email, role, status, created_at FROM pengguna ORDER BY id DESC");
    respond(true, "Daftar pengguna", $res->fetch_all(MYSQLI_ASSOC));
}

$in = getJsonInput();

if ($method === 'POST') {
    $username = trim($in['username'] ?? '');
    $password = $in['password'] ?? '123456';
    $nama = trim($in['nama_lengkap'] ?? '');
    $email = trim($in['email'] ?? '');
    $role = in_array($in['role'] ?? '', ['admin', 'dokter', 'pasien']) ? $in['role'] : 'pasien';
    $status = ($in['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';

    if ($username === '' || $nama === '') respond(false, "Username dan nama lengkap wajib diisi", null, 400);
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO pengguna (username, password_hash, nama_lengkap, email, role, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $hash, $nama, $email, $role, $status);
    if ($stmt->execute()) {
        respond(true, "Pengguna berhasil dibuat", ["id" => $conn->insert_id], 201);
    }
    respond(false, "Gagal membuat pengguna: " . $conn->error, null, 400);
}

if ($method === 'PUT') {
    $id = (int)($in['id'] ?? $_GET['id'] ?? 0);
    $nama = trim($in['nama_lengkap'] ?? '');
    $email = trim($in['email'] ?? '');
    $role = in_array($in['role'] ?? '', ['admin', 'dokter', 'pasien']) ? $in['role'] : 'pasien';
    $status = ($in['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';

    if ($id <= 0 || $nama === '') respond(false, "Data pengguna tidak valid", null, 400);

    if (!empty($in['password'])) {
        $hash = password_hash($in['password'], PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE pengguna SET nama_lengkap=?, email=?, role=?, status=?, password_hash=? WHERE id=?");
        $stmt->bind_param("sssssi", $nama, $email, $role, $status, $hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE pengguna SET nama_lengkap=?, email=?, role=?, status=? WHERE id=?");
        $stmt->bind_param("ssssi", $nama, $email, $role, $status, $id);
    }

    if ($stmt->execute()) {
        respond(true, "Pengguna berhasil diperbarui", ["id" => $id]);
    }
    respond(false, "Gagal memperbarui pengguna: " . $conn->error, null, 400);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? $in['id'] ?? 0);
    if ($id <= 0) respond(false, "ID pengguna tidak valid", null, 400);
    $stmt = $conn->prepare("DELETE FROM pengguna WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        respond(true, "Pengguna berhasil dihapus", ["id" => $id]);
    }
    respond(false, "Gagal menghapus pengguna: " . $conn->error, null, 400);
}

respond(false, "Metode tidak didukung", null, 405);
