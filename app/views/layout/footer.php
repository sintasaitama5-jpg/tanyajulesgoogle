    </main>
  </div><!-- end mainContent -->
</div><!-- end flex -->

<script>
function toggleSidebar() {
  const s = document.getElementById('sidebar');
  const o = document.getElementById('sidebarOverlay');
  s.classList.toggle('open');
  o.classList.toggle('open');
}
// Close on resize to desktop
window.addEventListener('resize', () => {
  if (window.innerWidth >= 1024) {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
  }
});
</script>
</body>
</html>
