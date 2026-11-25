<?php
session_start();
require_once __DIR__ . "/config/api_client.php";

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $resp = callApi("login.php", "POST", [
        "username" => $username,
        "password" => $password
    ]);

    if (!empty($resp['status']) && !empty($resp['data'])) {
        $_SESSION['user'] = $resp['data'];
        header("Location: dashboard.php");
        exit;
    }
    $error = $resp['message'] ?? "Login gagal";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — RS Harapan Sehat</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-xl space-y-6">
    <div>
      <span class="text-xs font-bold bg-teal-100 text-teal-800 px-2.5 py-1 rounded-full">Portal RS Harapan Sehat</span>
      <h1 class="text-2xl font-extrabold text-slate-900 mt-3">Masuk ke Sistem</h1>
      <p class="text-sm text-slate-500">Silakan masukkan username dan password akun Anda</p>
    </div>

    <?php if ($error): ?>
      <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Username</label>
        <input type="text" name="username" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" placeholder="Masukkan username" />
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
        <input type="password" name="password" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" placeholder="••••••••" />
      </div>
      <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold py-2.5 rounded-xl transition">Masuk</button>
    </form>

    <div class="bg-slate-50 p-3.5 rounded-xl text-xs text-slate-600 space-y-1 border border-slate-200">
      <div class="font-bold text-slate-800">Akun Demo (password: <code>password</code>):</div>
      <div>• Admin: <code>admin</code> | Dokter: <code>dr.budi</code> | Pasien: <code>pasien.andi</code></div>
    </div>

    <div class="text-center"><a href="index.php" class="text-xs text-slate-500 hover:text-teal-700">← Kembali ke Beranda</a></div>
  </div>
</body>
</html>
