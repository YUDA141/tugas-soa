<?php
require_once __DIR__ . "/config/api_client.php";
require_once __DIR__ . "/includes/header.php";
requireLogin();

$lap = callApi("laporan.php", "GET", [
    "tgl_awal" => date("Y-m-01"),
    "tgl_akhir" => date("Y-m-d")
]);
$ringkasan = $lap['data']['ringkasan'] ?? [
    "total_kunjungan" => 0,
    "total_pasien_terdaftar" => 0,
    "total_dokter" => 0,
    "total_poliklinik" => 0,
    "total_biaya" => 0,
    "total_ditanggung" => 0
];
$rekapPoli = $lap['data']['rekap_poliklinik'] ?? [];
$rekapAsuransi = $lap['data']['rekap_asuransi'] ?? [];
$kunjunganTerbaru = array_slice($lap['data']['detail_kunjungan'] ?? [], 0, 6);
?>

<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-2">
    <div>
      <h1 class="text-2xl font-extrabold text-slate-900">Dashboard <?= ucfirst(htmlspecialchars($role)) ?></h1>
      <p class="text-sm text-slate-500">Selamat datang, <strong><?= htmlspecialchars($user['nama_lengkap']) ?></strong>.</p>
    </div>
    <a href="kunjungan.php" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-sm font-semibold">+ Daftar / Kelola Kunjungan</a>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <div class="text-xs text-slate-500 font-semibold">Kunjungan Bulan Ini</div>
      <div class="text-3xl font-extrabold text-slate-900 mt-1"><?= (int)$ringkasan['total_kunjungan'] ?></div>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <div class="text-xs text-slate-500 font-semibold">Total Pasien</div>
      <div class="text-3xl font-extrabold text-teal-700 mt-1"><?= (int)$ringkasan['total_pasien_terdaftar'] ?></div>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <div class="text-xs text-slate-500 font-semibold">Dokter & Poliklinik</div>
      <div class="text-3xl font-extrabold text-slate-900 mt-1"><?= (int)$ringkasan['total_dokter'] ?> <span class="text-base font-normal text-slate-400">/ <?= (int)$ringkasan['total_poliklinik'] ?> Poli</span></div>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <div class="text-xs text-slate-500 font-semibold">Tanggungan Asuransi</div>
      <div class="text-2xl font-extrabold text-emerald-700 mt-1"><?= formatRupiah($ringkasan['total_ditanggung']) ?></div>
    </div>
  </div>

  <div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <h2 class="font-bold text-slate-900 mb-3">Kunjungan per Poliklinik</h2>
      <canvas id="chartPoli" height="160"></canvas>
    </div>
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <h2 class="font-bold text-slate-900 mb-3">Distribusi Penjamin / Asuransi</h2>
      <canvas id="chartAsuransi" height="160"></canvas>
    </div>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
    <h2 class="font-bold text-slate-900 mb-4">Kunjungan Terbaru</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left searchable-table">
        <thead class="bg-slate-50 text-slate-600 border-b">
          <tr>
            <th class="py-2.5 px-3">No. Kunjungan</th>
            <th class="py-2.5 px-3">Pasien</th>
            <th class="py-2.5 px-3">Poliklinik / Dokter</th>
            <th class="py-2.5 px-3">Asuransi</th>
            <th class="py-2.5 px-3">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php foreach ($kunjunganTerbaru as $k): ?>
            <tr>
              <td class="py-2.5 px-3 font-mono text-xs"><?= htmlspecialchars($k['no_kunjungan']) ?></td>
              <td class="py-2.5 px-3 font-semibold"><?= htmlspecialchars($k['nama_pasien']) ?></td>
              <td class="py-2.5 px-3"><?= htmlspecialchars($k['nama_poli']) ?> • <span class="text-slate-500"><?= htmlspecialchars($k['nama_dokter']) ?></span></td>
              <td class="py-2.5 px-3"><?= htmlspecialchars($k['nama_asuransi']) ?></td>
              <td class="py-2.5 px-3"><span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800"><?= htmlspecialchars($k['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  const poliLabels = <?= json_encode(array_column($rekapPoli, 'nama_poli')) ?>;
  const poliCounts = <?= json_encode(array_map('intval', array_column($rekapPoli, 'jumlah'))) ?>;
  new Chart(document.getElementById("chartPoli"), {
    type: "bar",
    data: {
      labels: poliLabels,
      datasets: [{ label: "Jumlah Kunjungan", data: poliCounts, borderRadius: 8 }]
    }
  });

  const asrLabels = <?= json_encode(array_column($rekapAsuransi, 'nama_asuransi')) ?>;
  const asrCounts = <?= json_encode(array_map('intval', array_column($rekapAsuransi, 'jumlah'))) ?>;
  new Chart(document.getElementById("chartAsuransi"), {
    type: "doughnut",
    data: {
      labels: asrLabels,
      datasets: [{ data: asrCounts }]
    }
  });
</script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
