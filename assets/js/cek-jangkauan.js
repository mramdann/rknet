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

  // ====== Form Isi Data: buka modal dari tombol hasil ======
  document.getElementById('btnIsiData').addEventListener('click', () => {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalHasil')).hide();
    const modalIsi = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalIsiData'));
    modalIsi.show();
    // Prefill kotak pencarian peta dengan alamat yang sudah dipilih
    if (lokasiTerpilih) document.getElementById('inputCariPeta').value = lokasiTerpilih.nama;
    muatProvinsi();
    document.dispatchEvent(new CustomEvent('isiDataDibuka'));   // dipakai modul peta (Leaflet)
  });

  // ====== Wilayah bertingkat via API emsifa ======
  const BASE_WILAYAH = 'https://www.emsifa.com/api-wilayah-indonesia/api';
  let provinsiSudahDimuat = false;

  async function ambilWilayah(path) {
    const res = await fetch(`${BASE_WILAYAH}/${path}.json`);
    return res.ok ? res.json() : [];
  }

  function isiSelect(sel, daftar, placeholder) {
    sel.innerHTML = `<option value="">${placeholder}</option>`
      + daftar.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
    sel.disabled = daftar.length === 0;
  }

  async function muatProvinsi() {
    if (provinsiSudahDimuat) return;
    const sel = document.getElementById('selProvinsi');
    try {
      isiSelect(sel, await ambilWilayah('provinces'), 'Pilih Provinsi Pemasangan');
      provinsiSudahDimuat = true;
    } catch (e) {
      sel.innerHTML = '<option value="">Gagal memuat provinsi</option>';
    }
  }

  const selProvinsi  = document.getElementById('selProvinsi');
  const selKota      = document.getElementById('selKota');
  const selKecamatan = document.getElementById('selKecamatan');
  const selKelurahan = document.getElementById('selKelurahan');

  selProvinsi.addEventListener('change', async () => {
    isiSelect(selKota, [], 'Memuat...'); selKota.disabled = true;
    isiSelect(selKecamatan, [], 'Pilih Kecamatan Pemasangan'); selKecamatan.disabled = true;
    isiSelect(selKelurahan, [], 'Pilih Desa / Kelurahan'); selKelurahan.disabled = true;
    if (!selProvinsi.value) return;
    isiSelect(selKota, await ambilWilayah(`regencies/${selProvinsi.value}`), 'Pilih Kota Pemasangan');
  });

  selKota.addEventListener('change', async () => {
    isiSelect(selKecamatan, [], 'Memuat...'); selKecamatan.disabled = true;
    isiSelect(selKelurahan, [], 'Pilih Desa / Kelurahan'); selKelurahan.disabled = true;
    if (!selKota.value) return;
    isiSelect(selKecamatan, await ambilWilayah(`districts/${selKota.value}`), 'Pilih Kecamatan Pemasangan');
  });

  selKecamatan.addEventListener('change', async () => {
    isiSelect(selKelurahan, [], 'Memuat...'); selKelurahan.disabled = true;
    if (!selKecamatan.value) return;
    isiSelect(selKelurahan, await ambilWilayah(`villages/${selKecamatan.value}`), 'Pilih Desa / Kelurahan');
  });

  // ====== Submit (UI only) ======
  document.getElementById('formIsiData').addEventListener('submit', (e) => {
    e.preventDefault();
    document.getElementById('formIsiData').classList.add('d-none');
    document.getElementById('suksesIsiData').classList.remove('d-none');
  });

  // ====== Peta Leaflet (penanda titik lokasi pemasangan) ======
  let peta = null, marker = null;
  // Ikon marker eksplisit dari CDN agar gambar tidak 404
  const ikonMarker = L.icon({
    iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-icon.png',
    iconRetinaUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    shadowUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-shadow.png',
    iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
  });

  function setKoordinat(lat, lon) {
    document.getElementById('koordinatTerpilih').textContent =
      `Koordinat: ${lat.toFixed(6)}, ${lon.toFixed(6)}`;
    window.koordinatPemasangan = { lat, lon };
  }

  function initPeta() {
    const pusat = lokasiTerpilih ? [lokasiTerpilih.lat, lokasiTerpilih.lon] : [-6.2, 106.816];
    peta = L.map('petaPemasangan').setView(pusat, 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19, attribution: '© OpenStreetMap'
    }).addTo(peta);
    marker = L.marker(pusat, { draggable: true, icon: ikonMarker }).addTo(peta);
    // Geser pin -> perbarui koordinat
    marker.on('dragend', () => {
      const p = marker.getLatLng();
      setKoordinat(p.lat, p.lng);
    });
    setKoordinat(pusat[0], pusat[1]);
  }

  function pindahPeta(lat, lon) {
    peta.setView([lat, lon], 16);
    marker.setLatLng([lat, lon]);
    setKoordinat(lat, lon);
  }

  // Saat modal tampil: inisialisasi peta lalu sesuaikan ukurannya (modal sebelumnya tersembunyi)
  document.getElementById('modalIsiData').addEventListener('shown.bs.modal', () => {
    if (!peta) initPeta();
    else if (lokasiTerpilih) pindahPeta(lokasiTerpilih.lat, lokasiTerpilih.lon);
    setTimeout(() => peta.invalidateSize(), 200);
  });

  // Tombol "Cari" pada peta: geocode lalu pindahkan pin
  document.getElementById('btnCariPeta').addEventListener('click', async () => {
    const kueri = document.getElementById('inputCariPeta').value.trim();
    if (kueri.length < 3) return;
    try {
      const hasil = await cariAlamat(kueri);
      if (hasil.length) pindahPeta(+hasil[0].lat, +hasil[0].lon);
    } catch (e) { /* abaikan */ }
  });
})();
