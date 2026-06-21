// cek-jangkauan.js — logika halaman cek jangkauan (autocomplete, cek, modal hasil).
(function () {
  const input    = document.getElementById('inputAlamat');
  const dropdown = document.getElementById('dropdownAlamat');
  const btnCek   = document.getElementById('btnCekJangkauan');
  let lokasiTerpilih = null;   // {lat, lon, nama}
  let timer = null;

  // Cari alamat memakai Nominatim (OpenStreetMap), dibatasi ke Indonesia
  async function cariAlamat(kueri) {
    const url = 'https://nominatim.openstreetmap.org/search'
      + '?format=jsonv2&countrycodes=id&addressdetails=1&limit=5&q=' + encodeURIComponent(kueri);
    const res = await fetch(url, { headers: { 'Accept-Language': 'id' } });
    return res.ok ? res.json() : [];
  }

  function namaUtama(item) {
    return (item.name && item.name.trim()) || item.display_name.split(',')[0];
  }

  function tampilkanDropdown(hasil) {
    if (!hasil.length) {
      dropdown.innerHTML = '<div class="opsi-info">Alamat tidak ditemukan</div>';
      dropdown.classList.remove('d-none');
      return;
    }
    dropdown.innerHTML = hasil.map((h, i) =>
      `<div class="opsi" data-i="${i}"><div class="fw-600">${namaUtama(h)}</div><small>${h.display_name}</small></div>`
    ).join('');
    dropdown.classList.remove('d-none');
    dropdown.querySelectorAll('.opsi').forEach(el => {
      el.addEventListener('click', () => pilihAlamat(hasil[+el.dataset.i]));
    });
  }

  function pilihAlamat(item) {
    lokasiTerpilih = { lat: +item.lat, lon: +item.lon, nama: item.display_name };
    window.lokasiTerpilih = lokasiTerpilih;   // dipakai modul peta (Leaflet)
    input.value = item.display_name;
    dropdown.classList.add('d-none');
    btnCek.disabled = false;
  }

  input.addEventListener('input', () => {
    btnCek.disabled = true;
    lokasiTerpilih = null;
    const kueri = input.value.trim();
    clearTimeout(timer);
    if (kueri.length < 3) { dropdown.classList.add('d-none'); return; }
    dropdown.innerHTML = '<div class="opsi-info">Sedang mencari...</div>';
    dropdown.classList.remove('d-none');
    timer = setTimeout(async () => {
      try { tampilkanDropdown(await cariAlamat(kueri)); }
      catch (e) { dropdown.innerHTML = '<div class="opsi-info">Gagal mencari alamat</div>'; }
    }, 450);
  });

  // Tutup dropdown bila klik di luar area pencarian
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.cari-wrap')) dropdown.classList.add('d-none');
  });

  // Logika dummy: cek apakah nama alamat memuat kata kunci kota terjangkau
  function apakahTerjangkau(nama) {
    const n = nama.toLowerCase();
    return (window.KEYWORD_TERJANGKAU || []).some(k => n.includes(k));
  }

  function tampilkanHasil(terjangkau) {
    const ikon  = document.getElementById('hasilIkon');
    const judul = document.getElementById('hasilJudul');
    const pesan = document.getElementById('hasilPesan');
    const btn   = document.getElementById('btnIsiData');
    if (terjangkau) {
      ikon.innerHTML  = '<i class="bi bi-check-circle-fill text-success"></i>';
      judul.textContent = 'Hore! Area Anda terjangkau';
      pesan.textContent = 'Jaringan fiber Starlite tersedia di lokasi Anda. Lanjutkan pendaftaran sekarang.';
      btn.textContent = 'Daftar Sekarang';
    } else {
      ikon.innerHTML  = '<i class="bi bi-emoji-frown text-warning"></i>';
      judul.textContent = 'Maaf, area Anda di luar jangkauan';
      pesan.textContent = 'Bantu kami menjangkau wilayah Anda dengan mengisi data berikut.';
      btn.textContent = 'Isi Data';
    }
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalHasil')).show();
  }

  btnCek.addEventListener('click', () => {
    if (!lokasiTerpilih) return;
    tampilkanHasil(apakahTerjangkau(lokasiTerpilih.nama));
  });
})();
