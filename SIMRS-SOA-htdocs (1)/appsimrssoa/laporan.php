<?php
require_once __DIR__ . "/config/api_client.php";
require_once __DIR__ . "/includes/header.php";
requireRole(["admin", "dokter"]);

$tglAwal = $_GET['tgl_awal'] ?? date("Y-m-01");
$tglAkhir = $_GET['tgl_akhir'] ?? date("Y-m-d");

$resp = callApi("laporan.php", "GET", [
    "tgl_awal" => $tglAwal,
    "tgl_akhir" => $tglAkhir
]);
$lap = $resp['data'] ?? [];
$ringkasan = $lap['ringkasan'] ?? [];
$detail = $lap['detail_kunjungan'] ?? [];
?>

<div class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-extrabold text-slate-900">Laporan Kunjungan & Pembiayaan</h1>
      <p class="text-sm text-slate-500">Periode <?= htmlspecialchars($tglAwal) ?> s/d <?= htmlspecialchars($tglAkhir) ?></p>
    </div>
    <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-semibold">🖨️ Cetak / Simpan PDF</button>
  </div>

  <form method="GET" class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-wrap items-end gap-4">
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Awal</label>
      <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tglAwal) ?>" class="border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Akhir</label>
      <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tglAkhir) ?>" class="border rounded-xl px-3 py-2 text-sm" />
    </div>
    <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-xl text-sm font-semibold">Filter Laporan</button>
  </form>

  <div class="grid sm:grid-cols-3 gap-4">
    <div class="bg-white p-4 rounded-2xl border"><div class="text-xs text-slate-500">Total Biaya Layanan</div><div class="text-xl font-extrabold mt-1"><?= formatRupiah($ringkasan['total_biaya'] ?? 0) ?></div></div>
    <div class="bg-white p-4 rounded-2xl border"><div class="text-xs text-slate-500">Total Ditanggung Asuransi</div><div class="text-xl font-extrabold text-emerald-700 mt-1"><?= formatRupiah($ringkasan['total_ditanggung'] ?? 0) ?></div></div>
    <div class="bg-white p-4 rounded-2xl border"><div class="text-xs text-slate-500">Total Dibayar Pasien</div><div class="text-xl font-extrabold text-slate-900 mt-1"><?= formatRupiah($ringkasan['total_pasien'] ?? 0) ?></div></div>
  </div>

  <div class="bg-white p-5 rounded-2xl border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm text-left searchable-table">
      <thead class="bg-slate-50 border-b">
        <tr>
          <th class="py-2 px-3">Tanggal</th>
          <th class="py-2 px-3">No. Kunjungan</th>
          <th class="py-2 px-3">Pasien</th>
          <th class="py-2 px-3">Poliklinik</th>
          <th class="py-2 px-3">Asuransi</th>
          <th class="py-2 px-3">Total</th>
          <th class="py-2 px-3">Ditanggung</th>
          <th class="py-2 px-3">Bayar Pasien</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($detail as $d): ?>
          <tr>
            <td class="py-2 px-3"><?= htmlspecialchars($d['tanggal_kunjungan']) ?></td>
            <td class="py-2 px-3 font-mono text-xs"><?= htmlspecialchars($d['no_kunjungan']) ?></td>
            <td class="py-2 px-3 font-semibold"><?= htmlspecialchars($d['nama_pasien']) ?></td>
            <td class="py-2 px-3"><?= htmlspecialchars($d['nama_poli']) ?></td>
            <td class="py-2 px-3"><?= htmlspecialchars($d['nama_asuransi']) ?></td>
            <td class="py-2 px-3"><?= formatRupiah($d['biaya_total']) ?></td>
            <td class="py-2 px-3 text-emerald-700"><?= formatRupiah($d['biaya_ditanggung']) ?></td>
            <td class="py-2 px-3 font-semibold"><?= formatRupiah($d['biaya_pasien']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
