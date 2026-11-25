<?php
require_once __DIR__ . "/config/api_client.php";
require_once __DIR__ . "/includes/header.php";
requireRole(["admin"]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? 'create';
    $payload = [
        "id" => (int)($_POST['id'] ?? 0),
        "kode_poli" => $_POST['kode_poli'] ?? '',
        "nama_poli" => $_POST['nama_poli'] ?? '',
        "lokasi_lantai" => $_POST['lokasi_lantai'] ?? 'Lantai 1',
        "deskripsi" => $_POST['deskripsi'] ?? ''
    ];
    if ($aksi === 'delete') {
        $r = callApi("poliklinik.php?id=" . (int)$_POST['id'], "DELETE");
    } elseif ($aksi === 'update') {
        $r = callApi("poliklinik.php", "PUT", $payload);
    } else {
        $r = callApi("poliklinik.php", "POST", $payload);
    }
    setFlash($r['status'] ? "success" : "error", $r['message'] ?? "Operasi selesai");
    header("Location: poliklinik.php");
    exit;
}

$list = callApi("poliklinik.php")['data'] ?? [];
$editData = null;
if (!empty($_GET['edit'])) {
    foreach ($list as $item) {
        if ((int)$item['id'] === (int)$_GET['edit']) { $editData = $item; break; }
    }
}
?>

<div class="space-y-6">
  <h1 class="text-2xl font-extrabold text-slate-900">Manajemen Poliklinik</h1>

  <form method="POST" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm grid md:grid-cols-4 gap-4">
    <input type="hidden" name="aksi" value="<?= $editData ? 'update' : 'create' ?>" />
    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>" />
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Kode Poli</label>
      <input type="text" name="kode_poli" value="<?= htmlspecialchars($editData['kode_poli'] ?? '') ?>" required placeholder="POL-04" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Nama Poliklinik</label>
      <input type="text" name="nama_poli" value="<?= htmlspecialchars($editData['nama_poli'] ?? '') ?>" required placeholder="Poli Saraf" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Lokasi / Lantai</label>
      <input type="text" name="lokasi_lantai" value="<?= htmlspecialchars($editData['lokasi_lantai'] ?? 'Lantai 1') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Deskripsi</label>
      <input type="text" name="deskripsi" value="<?= htmlspecialchars($editData['deskripsi'] ?? '') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div class="md:col-span-4 flex gap-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2 rounded-xl text-sm font-semibold"><?= $editData ? 'Simpan Perubahan' : 'Tambah Poliklinik' ?></button>
      <?php if ($editData): ?><a href="poliklinik.php" class="bg-slate-200 px-4 py-2 rounded-xl text-sm font-semibold">Batal</a><?php endif; ?>
    </div>
  </form>

  <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm text-left searchable-table">
      <thead class="bg-slate-50 text-slate-600 border-b">
        <tr>
          <th class="py-2.5 px-3">Kode</th>
          <th class="py-2.5 px-3">Nama Poliklinik</th>
          <th class="py-2.5 px-3">Lokasi</th>
          <th class="py-2.5 px-3">Deskripsi</th>
          <th class="py-2.5 px-3">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($list as $row): ?>
          <tr>
            <td class="py-2.5 px-3 font-mono text-xs font-bold"><?= htmlspecialchars($row['kode_poli']) ?></td>
            <td class="py-2.5 px-3 font-semibold"><?= htmlspecialchars($row['nama_poli']) ?></td>
            <td class="py-2.5 px-3"><?= htmlspecialchars($row['lokasi_lantai']) ?></td>
            <td class="py-2.5 px-3 text-slate-600"><?= htmlspecialchars($row['deskripsi']) ?></td>
            <td class="py-2.5 px-3 flex gap-2">
              <a href="poliklinik.php?edit=<?= $row['id'] ?>" class="text-teal-700 font-semibold hover:underline">Edit</a>
              <form method="POST" onsubmit="return confirm('Hapus poliklinik ini?')" class="inline">
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
