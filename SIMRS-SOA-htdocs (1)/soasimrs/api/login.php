<?php
require_once __DIR__ . "/../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, "Metode request harus POST", null, 405);
}

$in = getJsonInput();
$username = trim($in['username'] ?? '');
$password = $in['password'] ?? '';

if ($username === '' || $password === '') {
    respond(false, "Username dan password wajib diisi", null, 400);
}

$stmt = $conn->prepare("SELECT id, username, password_hash, nama_lengkap, email, role, status FROM pengguna WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user || $user['status'] !== 'aktif') {
    respond(false, "Akun tidak ditemukan atau tidak aktif", null, 401);
}

$valid = password_verify($password, $user['password_hash']) || hash_equals($user['password_hash'], $password);
if (!$valid) {
    respond(false, "Username atau password salah", null, 401);
}

$profilTerkait = null;
if ($user['role'] === 'dokter') {
    $q = $conn->prepare("SELECT d.*, p.nama_poli FROM dokter d LEFT JOIN poliklinik p ON d.poliklinik_id = p.id WHERE d.pengguna_id = ? LIMIT 1");
    $q->bind_param("i", $user['id']);
    $q->execute();
    $profilTerkait = $q->get_result()->fetch_assoc();
} elseif ($user['role'] === 'pasien') {
    $q = $conn->prepare("SELECT ps.*, a.nama_asuransi, a.persen_tanggungan FROM pasien ps LEFT JOIN asuransi a ON ps.asuransi_id = a.id WHERE ps.pengguna_id = ? LIMIT 1");
    $q->bind_param("i", $user['id']);
    $q->execute();
    $profilTerkait = $q->get_result()->fetch_assoc();
}

unset($user['password_hash']);
$user['profil'] = $profilTerkait;

respond(true, "Login berhasil", $user);
