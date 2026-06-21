    </div><!-- /portal-content -->
  </div><!-- /portal-main -->
</div><!-- /portal-wrap -->

<?php include __DIR__ . '/notif.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Buka/tutup sidebar pada tampilan mobile
  (function () {
    const sidebar = document.getElementById('portalSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const tombol  = document.getElementById('btnSidebar');
    function buka()  { sidebar.classList.add('tampil');  overlay.classList.add('tampil'); }
    function tutup() { sidebar.classList.remove('tampil'); overlay.classList.remove('tampil'); }
    if (tombol)  tombol.addEventListener('click', buka);
    if (overlay) overlay.addEventListener('click', tutup);
  })();
</script>
</body>
</html>
