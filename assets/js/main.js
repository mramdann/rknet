// main.js — interaksi UI
const nav = document.querySelector('.rk-navbar');
const onScroll = () => {
  if (window.scrollY > 40) nav.classList.add('scrolled');
  else nav.classList.remove('scrolled');
};
window.addEventListener('scroll', onScroll);
onScroll();

// Saat modal berlangganan dibuka dari tombol paket, isi pilihan paket otomatis
const modalLangganan = document.getElementById('modalLangganan');
if (modalLangganan) {
  modalLangganan.addEventListener('show.bs.modal', (e) => {
    const paket = e.relatedTarget && e.relatedTarget.getAttribute('data-paket');
    const pilihan = document.getElementById('pilihPaketLangganan');
    if (paket && pilihan) pilihan.value = paket;
  });
}

// Tampilkan pesan sukses pada form (UI only). sembunyikanForm=false untuk menjaga
// form tetap terlihat (mis. cek jangkauan agar bisa dicek ulang).
function pasangSukses(idForm, idSukses, sembunyikanForm = true) {
  const form = document.getElementById(idForm);
  const sukses = document.getElementById(idSukses);
  if (!form || !sukses) return;
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (sembunyikanForm) form.classList.add('d-none');
    sukses.classList.remove('d-none');
  });
}
pasangSukses('formLangganan', 'suksesLangganan');
pasangSukses('formRedeem', 'suksesRedeem');
pasangSukses('formJangkauan', 'hasilJangkauan', false);
