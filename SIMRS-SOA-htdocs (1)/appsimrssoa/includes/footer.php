</main>
      <footer class="bg-white border-t border-slate-200 px-6 py-3 text-xs text-slate-500 flex justify-between">
        <span>© <?= date('Y') ?> RS Harapan Sehat — Sistem Informasi Manajemen Rumah Sakit</span>
        <span>Layanan Terpadu</span>
      </footer>
    </div>
  </div>

  <script>
    const searchInput = document.getElementById("liveTableSearch");
    if (searchInput) {
      searchInput.addEventListener("input", function () {
        const kw = this.value.toLowerCase();
        document.querySelectorAll("table.searchable-table tbody tr").forEach(tr => {
          tr.style.display = tr.innerText.toLowerCase().includes(kw) ? "" : "none";
        });
      });
    }
  </script>
</body>
</html>
