<?php
require_once __DIR__ . "/config/api_client.php";
require_once __DIR__ . "/includes/header.php";
requireRole(["admin"]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? 'create';
    $payload = [
        "id" => (int)($_POST['id'] ?? 0),
        "pengguna_id" => $_POST['pengguna_id'] ?? '',
        "poliklinik_id" => (int)($_POST['poliklinik_id'] ?? 0),
        "nip_str" => $_POST['nip_str'] ?? '',
        "nama_dokter" => $_POST['nama_dokter'] ?? '',
        "spesialisasi" => $_POST['spesialisasi'] ?? '',
        "no_hp" => $_POST['no_hp'] ?? '',
        "tarif_konsultasi" => (float)($_POST['tarif_konsultasi'] ?? 100000),
        "jadwal_praktik" => $_POST['jadwal_praktik'] ?? ''
    ];
    if ($aksi === 'delete') {
        $r = callApi("dokter.php?id=" . (int)$_POST['id'], "DELETE");
    } elseif ($aksi === 'update') {
        $r = callApi("dokter.php", "PUT", $payload);
    } else {
        $r = callApi("dokter.php", "POST", $payload);
    }
    setFlash($r['status'] ? "success" : "error", $r['message'] ?? "Operasi selesai");
    header("Location: dokter.php");
    exit;
}

$dokterList = callApi("dokter.php")['data'] ?? [];
$poliList = callApi("poliklinik.php")['data'] ?? [];
$penggunaList = callApi("pengguna.php")['data'] ?? [];
$editData = null;
if (!empty($_GET['edit'])) {
    foreach ($dokterList as $item) {
        if ((int)$item['id'] === (int)$_GET['edit']) { $editData = $item; break; }
    }
}
?>

<div class="space-y-6">
  <h1 class="text-2xl font-extrabold text-slate-900">Manajemen Data Dokter</h1>

  <form method="POST" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm grid md:grid-cols-4 gap-4">
    <input type="hidden" name="aksi" value="<?= $editData ? 'update' : 'create' ?>" />
    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>" />
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">NIP / No. STR</label>
      <input type="text" name="nip_str" value="<?= htmlspecialchars($editData['nip_str'] ?? '') ?>" required placeholder="STR-100205" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs font-bold text-slate-600 mb-1">Nama Dokter & Gelar</label>
      <input type="text" name="nama_dokter" value="<?= htmlspecialchars($editData['nama_dokter'] ?? '') ?>" required placeholder="dr. Ahmad Fauzi, Sp.JP" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Poliklinik</label>
      <select name="poliklinik_id" required class="w-full border rounded-xl px-3 py-2 text-sm">
        <?php foreach ($poliList as $p): ?>
          <option value="<?= $p['id'] ?>" <?= ((int)($editData['poliklinik_id'] ?? 0) === (int)$p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama_poli']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Spesialisasi</label>
      <input type="text" name="spesialisasi" value="<?= htmlspecialchars($editData['spesialisasi'] ?? '') ?>" required class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Tarif Konsultasi (Rp)</label>
      <input type="number" name="tarif_konsultasi" value="<?= htmlspecialchars($editData['tarif_konsultasi'] ?? '150000') ?>" required class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">No. HP</label>
      <input type="text" name="no_hp" value="<?= htmlspecialchars($editData['no_hp'] ?? '') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Akun Pengguna (Opsional)</label>
      <select name="pengguna_id" class="w-full border rounded-xl px-3 py-2 text-sm">
        <option value="">-- Belum Ada Akun --</option>
        <?php foreach ($penggunaList as $u): ?>
          <?php if ($u['role'] === 'dokter'): ?>
            <option value="<?= $u['id'] ?>" <?= ((int)($editData['pengguna_id'] ?? 0) === (int)$u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['nama_lengkap']) ?>)</option>
          <?php endif; ?>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="md:col-span-4">
      <label class="block text-xs font-bold text-slate-600 mb-1">Jadwal Praktik</label>
      <input type="text" name="jadwal_praktik" value="<?= htmlspecialchars($editData['jadwal_praktik'] ?? 'Senin - Jumat (08:00 - 14:00)') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div class="md:col-span-4 flex gap-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2 rounded-xl text-sm font-semibold"><?= $editData ? 'Simpan Perubahan' : 'Tambah Dokter' ?></button>
      <?php if ($editData): ?><a href="dokter.php" class="bg-slate-200 px-4 py-2 rounded-xl text-sm font-semibold">Batal</a><?php endif; ?>
    </div>
  </form>

  <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm text-left searchable-table">
      <thead class="bg-slate-50 text-slate-600 border-b">
        <tr>
          <th class="py-2.5 px-3">STR</th>
          <th class="py-2.5 px-3">Nama Dokter</th>
          <th class="py-2.5 px-3">Poliklinik</th>
          <th class="py-2.5 px-3">Tarif</th>
          <th class="py-2.5 px-3">Jadwal</th>
          <th class="py-2.5 px-3">Akun</th>
          <th class="py-2.5 px-3">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($dokterList as $d): ?>
          <tr>
            <td class="py-2.5 px-3 font-mono text-xs"><?= htmlspecialchars($d['nip_str']) ?></td>
            <td class="py-2.5 px-3 font-semibold"><?= htmlspecialchars($d['nama_dokter']) ?><br><span class="text-xs text-slate-500"><?= htmlspecialchars($d['spesialisasi']) ?></span></td>
            <td class="py-2.5 px-3"><?= htmlspecialchars($d['nama_poli']) ?></td>
            <td class="py-2.5 px-3 font-semibold text-teal-700"><?= formatRupiah($d['tarif_konsultasi']) ?></td>
            <td class="py-2.5 px-3 text-xs"><?= htmlspecialchars($d['jadwal_praktik']) ?></td>
            <td class="py-2.5 px-3 text-xs"><?= htmlspecialchars($d['username'] ?? '-') ?></td>
            <td class="py-2.5 px-3 flex gap-2">
              <a href="dokter.php?edit=<?= $d['id'] ?>" class="text-teal-700 font-semibold hover:underline">Edit</a>
              <form method="POST" onsubmit="return confirm('Hapus dokter ini?')" class="inline">
                <input type="hidden" name="aksi" value="delete" />
                <input type="hidden" name="id" value="<?= $d['id'] ?>" />
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
