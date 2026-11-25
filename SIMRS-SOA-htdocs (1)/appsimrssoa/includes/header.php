<?php
require_once __DIR__ . "/auth.php";
$user = currentUser();
$role = $user['role'] ?? 'guest';
$flash = pullFlash();
$activePage = basename($_SERVER['PHP_SELF']);

$menus = [
    ["file" => "dashboard.php", "label" => "Dashboard", "icon" => "📊", "roles" => ["admin", "dokter", "pasien"]],
    ["file" => "kunjungan.php", "label" => "Kunjungan & Poli", "icon" => "🩺", "roles" => ["admin", "dokter", "pasien"]],
    ["file" => "pasien.php", "label" => "Data Pasien", "icon" => "🧑‍🤝‍🧑", "roles" => ["admin", "dokter"]],
    ["file" => "dokter.php", "label" => "Data Dokter", "icon" => "👨‍⚕️", "roles" => ["admin"]],
    ["file" => "poliklinik.php", "label" => "Poliklinik", "icon" => "🏥", "roles" => ["admin"]],
    ["file" => "asuransi.php", "label" => "Asuransi", "icon" => "🛡️", "roles" => ["admin"]],
    ["file" => "pengguna.php", "label" => "Manajemen Akun", "icon" => "🔐", "roles" => ["admin"]],
    ["file" => "laporan.php", "label" => "Laporan Pelayanan", "icon" => "📑", "roles" => ["admin", "dokter"]],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SIMRS RS Harapan Sehat</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col">
  <div class="flex flex-1">
    <aside class="w-64 bg-slate-900 text-slate-100 flex flex-col justify-between shrink-0">
      <div>
        <div class="p-5 border-b border-slate-800">
          <div class="flex items-center gap-2.5">
            <span class="bg-teal-500 text-slate-950 font-bold px-2.5 py-1 rounded-lg text-sm">RS</span>
            <div>
              <div class="font-bold text-base tracking-tight">RS Harapan Sehat</div>
              <div class="text-xs text-slate-400">Sistem Informasi Rumah Sakit</div>
            </div>
          </div>
        </div>
        <nav class="p-3 space-y-1">
          <?php foreach ($menus as $m): ?>
            <?php if (in_array($role, $m['roles'], true)): ?>
              <?php $isActive = ($activePage === $m['file']); ?>
              <a href="<?= $m['file'] ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition <?= $isActive ? 'bg-teal-500 text-slate-950 font-semibold shadow' : 'text-slate-300 hover:bg-slate-800' ?>">
                <span><?= $m['icon'] ?></span>
                <span><?= htmlspecialchars($m['label']) ?></span>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>
        </nav>
      </div>
      <div class="p-4 border-t border-slate-800 text-xs text-slate-400 space-y-2">
        <div class="text-slate-200 font-semibold truncate"><?= htmlspecialchars($user['nama_lengkap'] ?? 'Tamu') ?></div>
        <div class="inline-block px-2 py-0.5 rounded bg-slate-800 text-teal-400 uppercase font-semibold"><?= htmlspecialchars($role) ?></div>
        <div class="pt-1">
          <a href="logout.php" class="block text-center bg-rose-600/90 hover:bg-rose-600 text-white py-1.5 rounded-lg font-medium transition">Keluar (Logout)</a>
        </div>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
      <header class="bg-white border-b border-slate-200 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
          <span class="text-xs font-semibold text-slate-600">Portal Layanan Terintegrasi</span>
        </div>
        <div class="flex items-center gap-3 text-sm">
          <input id="liveTableSearch" type="text" placeholder="Cari cepat di tabel..." class="border border-slate-300 rounded-xl px-3.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500" />
          <a href="index.php" class="text-slate-600 hover:text-teal-700 font-medium">Beranda</a>
        </div>
      </header>

      <main class="p-6 flex-1">
        <?php if ($flash): ?>
          <div id="flashBanner" class="mb-5 px-4 py-3 rounded-xl border flex items-center justify-between <?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
            <span class="text-sm font-medium"><?= htmlspecialchars($flash['text']) ?></span>
            <button onclick="document.getElementById('flashBanner').remove()" class="text-xs font-bold px-2">✕</button>
          </div>
        <?php endif; ?>
