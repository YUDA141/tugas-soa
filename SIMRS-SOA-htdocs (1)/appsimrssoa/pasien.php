<?php
require_once __DIR__ . "/config/api_client.php";
require_once __DIR__ . "/includes/header.php";
requireRole(["admin", "dokter"]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'admin') {
    $aksi = $_POST['aksi'] ?? 'create';
    $payload = [
        "id" => (int)($_POST['id'] ?? 0),
        "pengguna_id" => $_POST['pengguna_id'] ?? '',
        "asuransi_id" => $_POST['asuransi_id'] ?? '',
        "no_rm" => $_POST['no_rm'] ?? '',
        "nik" => $_POST['nik'] ?? '',
        "no_polis" => $_POST['no_polis'] ?? '',
        "nama_pasien" => $_POST['nama_pasien'] ?? '',
        "jenis_kelamin" => $_POST['jenis_kelamin'] ?? 'L',
        "tanggal_lahir" => $_POST['tanggal_lahir'] ?? '2000-01-01',
        "no_hp" => $_POST['no_hp'] ?? '',
        "alamat" => $_POST['alamat'] ?? ''
    ];
    if ($aksi === 'delete') {
        $r = callApi("pasien.php?id=" . (int)$_POST['id'], "DELETE");
    } elseif ($aksi === 'update') {
        $r = callApi("pasien.php", "PUT", $payload);
    } else {
        $r = callApi("pasien.php", "POST", $payload);
    }
    setFlash($r['status'] ? "success" : "error", $r['message'] ?? "Operasi selesai");
    header("Location: pasien.php");
    exit;
}

$pasienList = callApi("pasien.php")['data'] ?? [];
$asuransiList = callApi("asuransi.php")['data'] ?? [];
$penggunaList = callApi("pengguna.php")['data'] ?? [];
$editData = null;
if (!empty($_GET['edit']) && $role === 'admin') {
    foreach ($pasienList as $p) {
        if ((int)$p['id'] === (int)$_GET['edit']) { $editData = $p; break; }
    }
}
?>

<div class="space-y-6">
  <h1 class="text-2xl font-extrabold text-slate-900">Manajemen Data Pasien</h1>

  <?php if ($role === 'admin'): ?>
    <form method="POST" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm grid md:grid-cols-4 gap-4">
      <input type="hidden" name="aksi" value="<?= $editData ? 'update' : 'create' ?>" />
      <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>" />
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">No. RM</label>
        <input type="text" name="no_rm" value="<?= htmlspecialchars($editData['no_rm'] ?? ('RM-' . date('Ymd-His'))) ?>" required class="w-full border rounded-xl px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">NIK</label>
        <input type="text" name="nik" value="<?= htmlspecialchars($editData['nik'] ?? '') ?>" required class="w-full border rounded-xl px-3 py-2 text-sm" />
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap Pasien</label>
        <input type="text" name="nama_pasien" value="<?= htmlspecialchars($editData['nama_pasien'] ?? '') ?>" required class="w-full border rounded-xl px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">Jenis Kelamin</label>
        <select name="jenis_kelamin" class="w-full border rounded-xl px-3 py-2 text-sm">
          <option value="L" <?= (($editData['jenis_kelamin'] ?? '') === 'L') ? 'selected' : '' ?>>Laki-laki (L)</option>
          <option value="P" <?= (($editData['jenis_kelamin'] ?? '') === 'P') ? 'selected' : '' ?>>Perempuan (P)</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($editData['tanggal_lahir'] ?? '1998-01-01') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">Asuransi (Opsional)</label>
        <select name="asuransi_id" class="w-full border rounded-xl px-3 py-2 text-sm">
          <option value="">-- Umum / Tanpa Asuransi --</option>
          <?php foreach ($asuransiList as $a): ?>
            <option value="<?= $a['id'] ?>" <?= ((int)($editData['asuransi_id'] ?? 0) === (int)$a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['nama_asuransi']) ?> (<?= (int)$a['persen_tanggungan'] ?>%)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">No. Polis / BPJS</label>
        <input type="text" name="no_polis" value="<?= htmlspecialchars($editData['no_polis'] ?? '') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">Akun Pengguna (Opsional)</label>
        <select name="pengguna_id" class="w-full border rounded-xl px-3 py-2 text-sm">
          <option value="">-- Belum Ada Akun --</option>
          <?php foreach ($penggunaList as $u): ?>
            <?php if ($u['role'] === 'pasien'): ?>
              <option value="<?= $u['id'] ?>" <?= ((int)($editData['pengguna_id'] ?? 0) === (int)$u['id']) ? 'selected' : '' ?>><?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['nama_lengkap']) ?>)</option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">No. HP</label>
        <input type="text" name="no_hp" value="<?= htmlspecialchars($editData['no_hp'] ?? '') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-bold text-slate-600 mb-1">Alamat</label>
        <input type="text" name="alamat" value="<?= htmlspecialchars($editData['alamat'] ?? '') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
      </div>
      <div class="md:col-span-4 flex gap-2">
        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2 rounded-xl text-sm font-semibold"><?= $editData ? 'Simpan Perubahan' : 'Tambah Pasien' ?></button>
        <?php if ($editData): ?><a href="pasien.php" class="bg-slate-200 px-4 py-2 rounded-xl text-sm font-semibold">Batal</a><?php endif; ?>
      </div>
    </form>
  <?php endif; ?>

  <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm text-left searchable-table">
      <thead class="bg-slate-50 text-slate-600 border-b">
        <tr>
          <th class="py-2.5 px-3">No. RM</th>
          <th class="py-2.5 px-3">Nama Pasien</th>
          <th class="py-2.5 px-3">NIK / JK</th>
          <th class="py-2.5 px-3">Asuransi</th>
          <th class="py-2.5 px-3">Akun</th>
          <?php if ($role === 'admin'): ?><th class="py-2.5 px-3">Aksi</th><?php endif; ?>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($pasienList as $p): ?>
          <tr>
            <td class="py-2.5 px-3 font-mono text-xs"><?= htmlspecialchars($p['no_rm']) ?></td>
            <td class="py-2.5 px-3 font-semibold"><?= htmlspecialchars($p['nama_pasien']) ?></td>
            <td class="py-2.5 px-3"><?= htmlspecialchars($p['nik']) ?> (<?= htmlspecialchars($p['jenis_kelamin']) ?>)</td>
            <td class="py-2.5 px-3"><?= htmlspecialchars($p['nama_asuransi'] ?? 'Umum') ?></td>
            <td class="py-2.5 px-3 text-xs"><?= htmlspecialchars($p['username'] ?? '-') ?></td>
            <?php if ($role === 'admin'): ?>
              <td class="py-2.5 px-3 flex gap-2">
                <a href="pasien.php?edit=<?= $p['id'] ?>" class="text-teal-700 font-semibold hover:underline">Edit</a>
                <form method="POST" onsubmit="return confirm('Hapus pasien ini?')" class="inline">
                  <input type="hidden" name="aksi" value="delete" />
                  <input type="hidden" name="id" value="<?= $p['id'] ?>" />
                  <button class="text-rose-600 font-semibold hover:underline">Hapus</button>
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
