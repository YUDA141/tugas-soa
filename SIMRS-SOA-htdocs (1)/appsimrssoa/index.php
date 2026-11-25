<?php
session_start();
require_once __DIR__ . "/config/api_client.php";
$poliResp = callApi("poliklinik.php");
$poliklinik = $poliResp['data'] ?? [];
$dokterResp = callApi("dokter.php");
$dokterList = $dokterResp['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RS Harapan Sehat — Portal Layanan Pasien & Manajemen</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">
  <nav class="bg-white border-b border-slate-200 sticky top-0 z-10">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="bg-teal-600 text-white font-bold px-3 py-1.5 rounded-xl text-sm">RS</span>
        <div>
          <span class="font-bold text-slate-900 block leading-none">RS Harapan Sehat</span>
          <span class="text-xs text-slate-500">Pelayanan Cepat, Ramah & Terpadu</span>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <a href="#poliklinik" class="hidden sm:inline text-sm font-medium text-slate-600 hover:text-teal-700">Poliklinik</a>
        <a href="#dokter" class="hidden sm:inline text-sm font-medium text-slate-600 hover:text-teal-700">Jadwal Dokter</a>
        <?php if (!empty($_SESSION['user'])): ?>
          <a href="dashboard.php" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow-sm">Buka Dashboard</a>
        <?php else: ?>
          <a href="login.php" class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2 rounded-xl text-sm font-semibold shadow-sm">Masuk / Login</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <section class="max-w-6xl mx-auto px-6 py-16 grid md:grid-cols-2 gap-10 items-center">
    <div class="space-y-5">
      <span class="inline-block bg-teal-100 text-teal-800 text-xs font-bold px-3 py-1 rounded-full">Pendaftaran Rawat Jalan & Rekam Medis</span>
      <h1 class="text-4xl font-extrabold text-slate-900 leading-tight">Kesehatan Anda Prioritas Utama Layanan Kami</h1>
      <p class="text-slate-600 leading-relaxed">Akses jadwal poliklinik, pendaftaran berobat online, klaim penjamin asuransi maupun BPJS, serta riwayat pemeriksaan medis dalam satu portal terpadu.</p>
      <div class="flex flex-wrap gap-3 pt-2">
        <a href="login.php" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-semibold shadow">Masuk ke Portal Layanan</a>
        <a href="#poliklinik" class="bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 px-6 py-3 rounded-xl font-semibold">Lihat Layanan Poli</a>
      </div>
    </div>

    <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-sm space-y-5">
      <div class="flex items-center justify-between border-b border-slate-100 pb-4">
        <div>
          <div class="text-xs font-bold uppercase tracking-wider text-teal-700">Informasi Layanan</div>
          <div class="text-lg font-bold text-slate-900 mt-0.5">Akses Sesuai Peran Pengguna</div>
        </div>
        <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full">Online 24 Jam</span>
      </div>
      <div class="space-y-3">
        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 flex items-start gap-3">
          <span class="text-xl">🧑‍⚕️</span>
          <div>
            <div class="font-bold text-slate-900 text-sm">Pasien & Peserta Asuransi</div>
            <div class="text-xs text-slate-600">Daftar kunjungan poliklinik, cek tanggungan BPJS/Asuransi, dan lihat hasil diagnosa.</div>
          </div>
        </div>
        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 flex items-start gap-3">
          <span class="text-xl">🩺</span>
          <div>
            <div class="font-bold text-slate-900 text-sm">Dokter Spesialis</div>
            <div class="text-xs text-slate-600">Kelola antrean pasien poliklinik dan catat diagnosa pemeriksaan secara langsung.</div>
          </div>
        </div>
        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 flex items-start gap-3">
          <span class="text-xl">🏥</span>
          <div>
            <div class="font-bold text-slate-900 text-sm">Administrator Rumah Sakit</div>
            <div class="text-xs text-slate-600">Kelola data master poliklinik, dokter, pasien, mitra asuransi, dan laporan keuangan.</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="poliklinik" class="max-w-6xl mx-auto px-6 pb-14">
    <h2 class="text-2xl font-bold text-slate-900 mb-6">Layanan Poliklinik</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php foreach ($poliklinik as $p): ?>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-teal-300 transition">
          <div class="text-xs font-bold text-teal-700"><?= htmlspecialchars($p['kode_poli']) ?> • <?= htmlspecialchars($p['lokasi_lantai']) ?></div>
          <h3 class="text-lg font-bold text-slate-900 mt-1"><?= htmlspecialchars($p['nama_poli']) ?></h3>
          <p class="text-sm text-slate-600 mt-2"><?= htmlspecialchars($p['deskripsi'] ?: 'Layanan rawat jalan terstandar.') ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="dokter" class="max-w-6xl mx-auto px-6 pb-16">
    <h2 class="text-2xl font-bold text-slate-900 mb-6">Dokter & Jadwal Praktik</h2>
    <div class="grid sm:grid-cols-2 gap-5">
      <?php foreach ($dokterList as $d): ?>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between gap-4">
          <div>
            <span class="text-xs font-semibold bg-teal-50 text-teal-700 px-2.5 py-0.5 rounded-full"><?= htmlspecialchars($d['nama_poli']) ?></span>
            <h3 class="text-base font-bold text-slate-900 mt-1.5"><?= htmlspecialchars($d['nama_dokter']) ?></h3>
            <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($d['jadwal_praktik']) ?></p>
          </div>
          <a href="login.php" class="shrink-0 text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white px-3.5 py-2 rounded-xl">Daftar</a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</body>
</html>
