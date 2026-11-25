<?php
require_once __DIR__ . "/config/api_client.php";
require_once __DIR__ . "/includes/header.php";
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? 'daftar';
    if ($aksi === 'daftar') {
        $pasienId = ($role === 'pasien') ? (int)($user['profil']['id'] ?? 0) : (int)($_POST['pasien_id'] ?? 0);
        $r = callApi("kunjungan.php", "POST", [
            "pasien_id" => $pasienId,
            "dokter_id" => (int)($_POST['dokter_id'] ?? 0),
            "tanggal_kunjungan" => $_POST['tanggal_kunjungan'] ?? date('Y-m-d'),
            "keluhan" => $_POST['keluhan'] ?? ''
        ]);
        setFlash($r['status'] ? "success" : "error", $r['message'] ?? "Selesai");
    } elseif ($aksi === 'diagnosa' && in_array($role, ['admin', 'dokter'], true)) {
        $r = callApi("kunjungan.php", "PUT", [
            "id" => (int)($_POST['id'] ?? 0),
            "diagnosa" => $_POST['diagnosa'] ?? '',
            "status" => $_POST['status'] ?? 'selesai'
        ]);
        setFlash($r['status'] ? "success" : "error", $r['message'] ?? "Diperbarui");
    }
    header("Location: kunjungan.php");
    exit;
}

$filterParams = [];
if ($role === 'pasien' && !empty($user['profil']['id'])) {
    $filterParams['pasien_id'] = (int)$user['profil']['id'];
}
$kunjunganList = callApi("kunjungan.php", "GET", $filterParams)['data'] ?? [];
$dokterList = callApi("dokter.php")['data'] ?? [];
$pasienList = callApi("pasien.php")['data'] ?? [];
?>

<div class="space-y-6">
  <h1 class="text-2xl font-extrabold text-slate-900">Pendaftaran & Layanan Kunjungan</h1>

  <form method="POST" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm grid md:grid-cols-4 gap-4">
    <input type="hidden" name="aksi" value="daftar" />
    <?php if ($role !== 'pasien'): ?>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">Pasien</label>
        <select name="pasien_id" required class="w-full border rounded-xl px-3 py-2 text-sm">
          <?php foreach ($pasienList as $ps): ?>
            <option value="<?= $ps['id'] ?>"><?= htmlspecialchars($ps['no_rm']) ?> — <?= htmlspecialchars($ps['nama_pasien']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Dokter & Poliklinik</label>
      <select name="dokter_id" required class="w-full border rounded-xl px-3 py-2 text-sm">
        <?php foreach ($dokterList as $dk): ?>
          <option value="<?= $dk['id'] ?>"><?= htmlspecialchars($dk['nama_dokter']) ?> (<?= htmlspecialchars($dk['nama_poli']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Kunjungan</label>
      <input type="date" name="tanggal_kunjungan" value="<?= date('Y-m-d') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div class="<?= $role === 'pasien' ? 'md:col-span-2' : 'md:col-span-4' ?>">
      <label class="block text-xs font-bold text-slate-600 mb-1">Keluhan Utama</label>
      <input type="text" name="keluhan" required placeholder="Tuliskan keluhan pasien..." class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div class="md:col-span-4">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2 rounded-xl text-sm font-semibold">Daftarkan Kunjungan</button>
    </div>
  </form>

  <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm text-left searchable-table">
      <thead class="bg-slate-50 text-slate-600 border-b">
        <tr>
          <th class="py-2.5 px-3">No. / Tgl</th>
          <th class="py-2.5 px-3">Pasien</th>
          <th class="py-2.5 px-3">Poli & Dokter</th>
          <th class="py-2.5 px-3">Keluhan & Diagnosa</th>
          <th class="py-2.5 px-3">Biaya / Tanggungan</th>
          <th class="py-2.5 px-3">Status</th>
          <?php if (in_array($role, ['admin', 'dokter'], true)): ?><th class="py-2.5 px-3">Pemeriksaan</th><?php endif; ?>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($kunjunganList as $k): ?>
          <tr>
            <td class="py-2.5 px-3 text-xs"><div class="font-mono font-bold"><?= htmlspecialchars($k['no_kunjungan']) ?></div><div><?= htmlspecialchars($k['tanggal_kunjungan']) ?></div></td>
            <td class="py-2.5 px-3 font-semibold"><?= htmlspecialchars($k['nama_pasien']) ?></td>
            <td class="py-2.5 px-3"><?= htmlspecialchars($k['nama_poli']) ?><br><span class="text-xs text-slate-500"><?= htmlspecialchars($k['nama_dokter']) ?></span></td>
            <td class="py-2.5 px-3 text-xs"><div><strong>Keluhan:</strong> <?= htmlspecialchars($k['keluhan']) ?></div><div><strong>Diagnosa:</strong> <?= htmlspecialchars($k['diagnosa'] ?: '-') ?></div></td>
            <td class="py-2.5 px-3 text-xs"><div>Total: <?= formatRupiah($k['biaya_total']) ?></div><div class="text-emerald-700">Asuransi: <?= formatRupiah($k['biaya_ditanggung']) ?></div><div class="font-semibold">Pasien: <?= formatRupiah($k['biaya_pasien']) ?></div></td>
            <td class="py-2.5 px-3"><span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800"><?= htmlspecialchars($k['status']) ?></span></td>
            <?php if (in_array($role, ['admin', 'dokter'], true)): ?>
              <td class="py-2.5 px-3">
                <form method="POST" class="space-y-1.5 min-w-[180px]">
                  <input type="hidden" name="aksi" value="diagnosa" />
                  <input type="hidden" name="id" value="<?= $k['id'] ?>" />
                  <input type="text" name="diagnosa" value="<?= htmlspecialchars($k['diagnosa'] ?? '') ?>" placeholder="Isi diagnosa..." class="w-full border rounded-lg px-2 py-1 text-xs" />
                  <div class="flex gap-1">
                    <select name="status" class="border rounded-lg px-2 py-1 text-xs flex-1">
                      <?php foreach (['menunggu', 'diperiksa', 'selesai', 'batal'] as $st): ?>
                        <option value="<?= $st ?>" <?= $k['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button class="bg-slate-900 text-white px-2.5 py-1 rounded-lg text-xs font-semibold">Simpan</button>
                  </div>
                </form>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
