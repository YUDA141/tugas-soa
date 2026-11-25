<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (empty($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
}

function requireRole(array $allowedRoles) {
    requireLogin();
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        echo "<div style='font-family:sans-serif;max-width:520px;margin:80px auto;padding:24px;border:1px solid #fecaca;background:#fef2f2;border-radius:12px;color:#991b1b;'>
                <h2>403 - Akses Ditolak</h2>
                <p>Peran <strong>" . htmlspecialchars($role) . "</strong> tidak memiliki hak akses untuk membuka halaman ini.</p>
                <a href='dashboard.php'>Kembali ke Dashboard</a>
              </div>";
        exit;
    }
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function setFlash($type, $text) {
    $_SESSION['flash'] = ["type" => $type, "text" => $text];
}

function pullFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
