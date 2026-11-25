<?php
require_once __DIR__ . "/config/api_client.php";
require_once __DIR__ . "/includes/header.php";
requireRole(["admin"]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? 'create';
    $payload = [
        "id" => (int)($_POST['id'] ?? 0),
        "kode_asuransi" => $_POST['kode_asuransi'] ?? '',
        "nama_asuransi" => $_POST['nama_asuransi'] ?? '',
        "jenis" => $_POST['jenis'] ?? 'Swasta',
        "persen_tanggungan" => (int)($_POST['persen_tanggungan'] ?? 80),
        "kontak" => $_POST['kontak'] ?? ''
    ];
    if ($aksi === 'delete') {
        $r = callApi("asuransi.php?id=" . (int)$_POST['id'], "DELETE");
    } elseif ($aksi === 'update') {
        $r = callApi("asuransi.php", "PUT", $payload);
    } else {
        $r = callApi("asuransi.php", "POST", $payload);
    }
    setFlash($r['status'] ? "success" : "error", $r['message'] ?? "Operasi selesai");
    header("Location: asuransi.php");
    exit;
}

$list = callApi("asuransi.php")['data'] ?? [];
$editData = null;
if (!empty($_GET['edit'])) {
    foreach ($list as $item) {
        if ((int)$item['id'] === (int)$_GET['edit']) { $editData = $item; break; }
    }
}
?>

<div class="space-y-6">
  <h1 class="text-2xl font-extrabold text-slate-900">Manajemen Mitra Asuransi</h1>

  <form method="POST" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm grid md:grid-cols-5 gap-4">
    <input type="hidden" name="aksi" value="<?= $editData ? 'update' : 'create' ?>" />
    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>" />
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Kode Asuransi</label>
      <input type="text" name="kode_asuransi" value="<?= htmlspecialchars($editData['kode_asuransi'] ?? '') ?>" required placeholder="ASR-AXA" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Nama Asuransi</label>
      <input type="text" name="nama_asuransi" value="<?= htmlspecialchars($editData['nama_asuransi'] ?? '') ?>" required placeholder="AXA Mandiri" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Jenis</label>
      <select name="jenis" class="w-full border rounded-xl px-3 py-2 text-sm">
        <?php foreach (['BPJS', 'Swasta', 'Perusahaan'] as $j): ?>
          <option value="<?= $j ?>" <?= (($editData['jenis'] ?? '') === $j) ? 'selected' : '' ?>><?= $j ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Tanggungan (%)</label>
      <input type="number" min="0" max="100" name="persen_tanggungan" value="<?= htmlspecialchars($editData['persen_tanggungan'] ?? '80') ?>" required class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Kontak Call Center</label>
      <input type="text" name="kontak" value="<?= htmlspecialchars($editData['kontak'] ?? '') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div class="md:col-span-5 flex gap-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2 rounded-xl text-sm font-semibold"><?= $editData ? 'Simpan Perubahan' : 'Tambah Asuransi' ?></button>
      <?php if ($editData): ?><a href="asuransi.php" class="bg-slate-200 px-4 py-2 rounded-xl text-sm font-semibold">Batal</a><?php endif; ?>
    </div>
  </form>

  <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm text-left searchable-table">
      <thead class="bg-slate-50 text-slate-600 border-b">
        <tr>
          <th class="py-2.5 px-3">Kode</th>
          <th class="py-2.5 px-3">Nama Asuransi</th>
          <th class="py-2.5 px-3">Jenis</th>
          <th class="py-2.5 px-3">Tanggungan</th>
          <th class="py-2.5 px-3">Kontak</th>
          <th class="py-2.5 px-3">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($list as $row): ?>
          <tr>
            <td class="py-2.5 px-3 font-mono text-xs font-bold"><?= htmlspecialchars($row['kode_asuransi']) ?></td>
            <td class="py-2.5 px-3 font-semibold"><?= htmlspecialchars($row['nama_asuransi']) ?></td>
            <td class="py-2.5 px-3"><?= htmlspecialchars($row['jenis']) ?></td>
            <td class="py-2.5 px-3 font-bold text-emerald-700"><?= (int)$row['persen_tanggungan'] ?>%</td>
            <td class="py-2.5 px-3"><?= htmlspecialchars($row['kontak']) ?></td>
            <td class="py-2.5 px-3 flex gap-2">
              <a href="asuransi.php?edit=<?= $row['id'] ?>" class="text-teal-700 font-semibold hover:underline">Edit</a>
              <form method="POST" onsubmit="return confirm('Hapus asuransi ini?')" class="inline">
                <input type="hidden" name="aksi" value="delete" />
                <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                <button class="text-rose-600 font-semibold hover:underline">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
