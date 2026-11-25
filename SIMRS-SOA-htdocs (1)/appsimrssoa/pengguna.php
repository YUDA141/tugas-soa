<?php
require_once __DIR__ . "/config/api_client.php";
require_once __DIR__ . "/includes/header.php";
requireRole(["admin"]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? 'create';
    $payload = [
        "id" => (int)($_POST['id'] ?? 0),
        "username" => $_POST['username'] ?? '',
        "password" => $_POST['password'] ?? '',
        "nama_lengkap" => $_POST['nama_lengkap'] ?? '',
        "email" => $_POST['email'] ?? '',
        "role" => $_POST['role'] ?? 'pasien',
        "status" => $_POST['status'] ?? 'aktif'
    ];
    if ($aksi === 'delete') {
        $r = callApi("pengguna.php?id=" . (int)$_POST['id'], "DELETE");
    } elseif ($aksi === 'update') {
        $r = callApi("pengguna.php", "PUT", $payload);
    } else {
        $r = callApi("pengguna.php", "POST", $payload);
    }
    setFlash($r['status'] ? "success" : "error", $r['message'] ?? "Operasi selesai");
    header("Location: pengguna.php");
    exit;
}

$list = callApi("pengguna.php")['data'] ?? [];
$editData = null;
if (!empty($_GET['edit'])) {
    foreach ($list as $item) {
        if ((int)$item['id'] === (int)$_GET['edit']) { $editData = $item; break; }
    }
}
?>

<div class="space-y-6">
  <h1 class="text-2xl font-extrabold text-slate-900">Manajemen Pengguna & Hak Akses</h1>

  <form method="POST" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm grid md:grid-cols-3 gap-4">
    <input type="hidden" name="aksi" value="<?= $editData ? 'update' : 'create' ?>" />
    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>" />
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($editData['username'] ?? '') ?>" <?= $editData ? 'readonly class="w-full border rounded-xl px-3 py-2 text-sm bg-slate-100"' : 'required class="w-full border rounded-xl px-3 py-2 text-sm"' ?> />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Password <?= $editData ? '(Kosongkan jika tetap)' : '' ?></label>
      <input type="password" name="password" <?= $editData ? '' : 'required' ?> placeholder="Minimal 6 karakter" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap</label>
      <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($editData['nama_lengkap'] ?? '') ?>" required class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($editData['email'] ?? '') ?>" class="w-full border rounded-xl px-3 py-2 text-sm" />
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Role Pengguna</label>
      <select name="role" class="w-full border rounded-xl px-3 py-2 text-sm">
        <?php foreach (['admin', 'dokter', 'pasien'] as $rl): ?>
          <option value="<?= $rl ?>" <?= (($editData['role'] ?? 'pasien') === $rl) ? 'selected' : '' ?>><?= strtoupper($rl) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs font-bold text-slate-600 mb-1">Status Akun</label>
      <select name="status" class="w-full border rounded-xl px-3 py-2 text-sm">
        <option value="aktif" <?= (($editData['status'] ?? 'aktif') === 'aktif') ? 'selected' : '' ?>>Aktif</option>
        <option value="nonaktif" <?= (($editData['status'] ?? '') === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
      </select>
    </div>
    <div class="md:col-span-3 flex gap-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2 rounded-xl text-sm font-semibold"><?= $editData ? 'Simpan Perubahan' : 'Buat Akun Pengguna' ?></button>
      <?php if ($editData): ?><a href="pengguna.php" class="bg-slate-200 px-4 py-2 rounded-xl text-sm font-semibold">Batal</a><?php endif; ?>
    </div>
  </form>

  <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm text-left searchable-table">
      <thead class="bg-slate-50 text-slate-600 border-b">
        <tr>
          <th class="py-2.5 px-3">Username</th>
          <th class="py-2.5 px-3">Nama Lengkap</th>
          <th class="py-2.5 px-3">Email</th>
          <th class="py-2.5 px-3">Role</th>
          <th class="py-2.5 px-3">Status</th>
          <th class="py-2.5 px-3">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($list as $u): ?>
          <tr>
            <td class="py-2.5 px-3 font-mono text-xs font-bold"><?= htmlspecialchars($u['username']) ?></td>
            <td class="py-2.5 px-3 font-semibold"><?= htmlspecialchars($u['nama_lengkap']) ?></td>
            <td class="py-2.5 px-3"><?= htmlspecialchars($u['email']) ?></td>
            <td class="py-2.5 px-3"><span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-800 uppercase"><?= htmlspecialchars($u['role']) ?></span></td>
            <td class="py-2.5 px-3"><span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $u['status'] === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>"><?= htmlspecialchars($u['status']) ?></span></td>
            <td class="py-2.5 px-3 flex gap-2">
              <a href="pengguna.php?edit=<?= $u['id'] ?>" class="text-teal-700 font-semibold hover:underline">Edit</a>
              <?php if ((int)$u['id'] !== (int)($user['id'] ?? 0)): ?>
                <form method="POST" onsubmit="return confirm('Hapus pengguna ini?')" class="inline">
                  <input type="hidden" name="aksi" value="delete" />
                  <input type="hidden" name="id" value="<?= $u['id'] ?>" />
                  <button class="text-rose-600 font-semibold hover:underline">Hapus</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
