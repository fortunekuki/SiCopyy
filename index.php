<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">
  <title>Sicopy - Peta Fotokopi Lengkap</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.locatecontrol/dist/L.Control.Locate.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
  <style>
    body {
      font-family: Outfit, sans-serif;
      background: #fdf6f0;
      margin: 0;
    }

    header {
      background: #ffc2d1;
      color: #333;
      padding: 15px;
      text-align: center;
      font-size: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
    }
    header img {
      width: 50px;
      margin-right: 10px;
    }
    #map {
      height: 500px;
      width: 80%;
      margin: 20px auto;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }
    .search-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      margin: 20px;
    }

    .search-container input,
    .search-container select,
    .search-container button {
      margin: 5px;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 10px;
      background: #fff;
    }
    .search-container button {
      background-color: #ffd6e0;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .search-container button:hover {
      background-color: #ffc2d1;
    }
    .filter-group {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-top: 10px;
    }
    .filter-facilities {
      display: flex;
      justify-content: center;
      gap: 15px;
      flex-wrap: wrap;
      margin-bottom: 15px;
    }
    .filter-row {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin: 8px;
    }
    .filter-row input,
    .filter-row select,
    .filter-row button {
      border-radius: 10px;
      padding: 8px;
      border: 1px solid #ccc;
    }
    .filter-row button {
      background-color: #cdeac0;
      cursor: pointer;
    }
    .filter-row button:hover {
      background-color: #b4dfb0;
    }
    #resultTable {
      width: 80%;
      margin: 20px auto;
      border-collapse: collapse;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    #resultTable th {
      background: #ffd6e0;
      padding: 10px;
    }

    #radiusSelector {
      min-width: 160px;
    }


    #resultTable td {
      padding: 10px;
      border-top: 1px solid #eee;
    }
  </style>
</head>
<body>
  <header>
    <img src="https://cdn.pixabay.com/animation/2024/05/16/21/45/21-45-34-3_512.gif" alt="Logo" />
    Sicopy - Peta Fotokopi
  </header>

  <div class="search-container">
  <input type="text" id="searchBox" placeholder="Cari fotokopi..." list="searchResults">
  <datalist id="searchResults"></datalist>
  <button onclick="showCheapest()">👇 Fotokopi Termurah</button>
  <button onclick="getNearest()">📍 Fotokopi Terdekat</button>
  <button onclick="showWithinRadius()">📍 Tampilkan Radius</button>
</div>

  <div class="filter-group">
    <div><strong>Filter Fasilitas:</strong></div>
    <div class="filter-facilities">
      <label><input type="checkbox" class="fasilitas" value="photocopy"> Photocopy</label>
      <label><input type="checkbox" class="fasilitas" value="print"> Print</label>
      <label><input type="checkbox" class="fasilitas" value="alat tulis"> Alat Tulis</label>
      <label><input type="checkbox" class="fasilitas" value="jilid"> Jilid</label>
    </div>
    <div class="filter-row">
      <label>Jam Buka Sebelum:</label>
      <input type="time" id="jamBukaFilter" value="08:00">
    </div>
    <div class="filter-row">
      <label>Order Online:</label>
      <select id="onlineFilter">
        <option value="">Semua</option>
        <option value="true">Ya</option>
        <option value="false">Tidak</option>
      </select>
    </div>
    <div class="filter-row">
      <label>Rating Minimal:</label>
      <input type="number" id="ratingFilter" step="0.1" min="0" max="5" value="0">
    </div>
    <div class="filter-row">
  <button onclick="applyFilter()">Terapkan Filter</button>
  <button onclick="resetFilter()">Reset Filter</button>
  <button onclick="routeFiltered()">Rute Lokasi Terpilih</button>
</div>

  <div id="map"></div>

  <table id="resultTable">
    <thead>
      <tr>
        <th>Nama</th>
        <th>Harga</th>
        <th>Jam Buka</th>
        <th>Order Online</th>
        <th>Rating</th>
        <th>Jam Sibuk</th>
        <th>Waktu Tunggu</th>
        <th>Jumlah Mesin</th>
        <th>Cocok Untuk</th>
      </tr>
    </thead>
    <tbody id="resultTableBody">
      <tr><td colspan="9" style="text-align:center;">Hasil akan muncul di sini</td></tr>
    </tbody>
  </table>  

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet.locatecontrol/dist/L.Control.Locate.min.js"></script>
  <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.min.js"></script>
  <script>
    const photocopyLocations = [
      {
        name: "Tunggal Photocopy Sukapura",
        lat: -6.971841928582177,
        lon: 107.63348971522502,
        price: "Rp500 - Rp8000",
        minPrice: 100,
        busyHours: ["10:00", "14:00"],
        estimatedWait: 5,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "5 komputer ready",
        fasilitas: ["photocopy", "print", "jilid", "alat tulis"],
        jamBuka: "06:10",
        jamTutup: "22:00",
        onlineOrder: true,
        rating: 4.7
      },
      {
        name: "Permata Copy & Printer",
        lat: -6.972091351449559,
        lon: 107.6338024454444,
        price: "Rp800 - Rp2000",
        minPrice: 800,
        busyHours: ["08:00", "10:00"],
        estimatedWait: 3,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "6 komputer ready",
        fasilitas: ["photocopy", "print"],
        jamBuka: "09:00",
        jamTutup: "21:00",
        onlineOrder: false,
        rating: 3.5
      },
      {
        name: "Trapesium Copy Center And Stationery",
        lat: -6.972152586072627,
        lon: 107.63395801356874,
        price: "Rp1000 - Rp5000",
        minPrice: 1000,
        busyHours: ["15:00", "17:00"],
        estimatedWait: 10,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "2 komputer ready",
        fasilitas: ["photocopy", "print", "komputer", "print mandiri"],
        jamBuka: "07:30",
        jamTutup: "23:00",
        onlineOrder: true,
        rating: 3.8
      },
      {
        name: "3L Photocopy & Stationery",
        lat: -6.971670695996494,
        lon: 107.63357177546693,
        price: "Rp300 - Rp2000",
        minPrice: 300,
        busyHours: ["06:00", "10:00"],
        estimatedWait: 7,
        cocokUntuk: ["mahasiswa, anak sekolah"],
        mesinStatus: " komputer ready",
        fasilitas: ["photocopy", "print", "alat tulis"],
        jamBuka: "06:30",
        jamTutup: "23:30",
        onlineOrder: true,
        rating: 5.0
      },
      {
        name: "Calisto FC",
        lat: -6.972288367167851,
        lon: 107.63402506879343,
        price: "Rp400 - Rp7000",
        minPrice: 400,
        busyHours: ["15.00", "19:00"],
        estimatedWait: 5,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "2 komputer ready",
        fasilitas: ["photocopy", "print", "alat tulis"],
        jamBuka: "07:00",
        jamTutup: "22:00",
        onlineOrder: true,
        rating: 4.6
      },
      {
        name: "Pratama Photocopy & Print",
        lat: -6.972212547828387,
        lon: 107.63469274812154,
        price: "Rp300 - Rp1400",
        minPrice: 300,
        busyHours: ["12:00", "15:00"],
        estimatedWait: 10,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "2 komputer ready",
        fasilitas: ["photocopy", "print"],
        jamBuka: "07:00",
        jamTutup: "21:00",
        onlineOrder: true,
        rating: 4.0
      },
      {
        name: "Anugerah Abadi Copy Centre",
        lat: -6.972708152966837,
        lon: 107.63602031737358,
        price: "Rp1500 - Rp2500",
        minPrice: 1500,
        busyHours: ["16:00", "18:00"],
        estimatedWait: 5,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "5 komputer ready",
        fasilitas: ["photocopy", "print", "alat tulis lengkap"],
        jamBuka: "08:00",
        jamTutup: "22:00",
        onlineOrder: true,
        rating: 4.4
      },
      {
        name: "Fotocopy PGA",
        lat: -6.974252473514358,
        lon: 107.6327387293989,
        price: "Rp500 - Rp1500",
        minPrice: 500,
        busyHours: ["07:00", "09:00"],
        estimatedWait: 15,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "1 komputer ready",
        fasilitas: ["photocopy", "print"],
        jamBuka: "00:00",
        jamTutup: "23:59",
        onlineOrder: true,
        rating: 4.3
      },
      {
        name: "LOW-COST PRINT & PHOTOCOPY",
        lat: -6.975807428940796,
        lon: 107.63321288308455,
        price: "Rp700 - Rp1500",
        minPrice: 700,
        busyHours: ["14:00", "17:00"],
        estimatedWait: 2,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "2 komputer ready",
        fasilitas: ["photocopy", "print", "komputer", "print mandiri"],
        jamBuka: "07:00",
        jamTutup: "23:00",
        onlineOrder: true,
        rating: 5.0
      },
      {
        name: "Tunggal Digital Photocopy & Printing",
        lat: -6.976316921417797,
        lon: 107.63310652962141,
        price: "Rp500 - Rp10000",
        minPrice: 500,
        busyHours: ["16:00", "20:00"],
        estimatedWait: 3,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "10 komputer ready",
        fasilitas: ["photocopy", "print", "jilid buku", "jilid ring", "laminating", "ATK lengkap", "art supply", "spanduk", "poster"],
        jamBuka: "06:00",
        jamTutup: "22:00",
        onlineOrder: true,
        rating: 4.5
      },
      {
        name: "Nugraha Ajie Photocopy",
        lat: -6.977310843604698,
        lon: 107.63268323729005,
        price: "Rp700 - Rp2000",
        minPrice: 700,
        busyHours: ["12:00", "14:00"],
        estimatedWait: 15,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "2 komputer ready",
        fasilitas: ["photocopy", "print", "jilid buku", "laminating", "hard cover", "soft cover", "alat tulis"],
        jamBuka: "06:30",
        jamTutup: "21:00",
        onlineOrder: true,
        rating: 4.6
      },
      {
        name: "Trivos Copy Center",
        lat: -6.978227717916646,
        lon: 107.6323836692545,
        price: "Rp500 - Rp2000",
        minPrice: 500,
        busyHours: ["14:00", "15:00"],
        estimatedWait: 15,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "1 komputer ready",
        fasilitas: ["photocopy", "print ukuran A4", "Menjual ATK lengkap"],
        jamBuka: "07:00",
        jamTutup: "22:00",
        onlineOrder: true,
        rating: 3.0
      },
      {
        name: "Putri Fotocopy Center",
        lat: -6.979474816879255,
        lon: 107.62986864685911,
        price: "Rp700 - Rp1500",
        minPrice: 700,
        busyHours: ["08:00", "10:00"],
        estimatedWait: 15,
        cocokUntuk: ["anak sekolah"],
        mesinStatus: "1 komputer ready",
        fasilitas: ["photocopy", "print ukuran A4"],
        jamBuka: "08:00",
        jamTutup: "21:00",
        onlineOrder: false,
        rating: 2.0
      },
      {
        name: "Dua Saudara Fotocopy",
        lat: -6.984386458767902,
        lon: 107.63189949829834,
        price: "Rp1000 - Rp5000",
        minPrice: 1000,
        busyHours: ["06:00", "09:00"],
        estimatedWait: 15,
        cocokUntuk: ["mahasiswa", "anak sekolah"],
        mesinStatus: "2 komputer ready",
        fasilitas: ["photocopy", "print ukuran A4", "cetak undangan", "photo", "kartu nama", "Menjual ATK"],
        jamBuka: "07:00",
        jamTutup: "17:00",
        onlineOrder: false,
        rating: 5.0 
      },
      {
        name: "Setia Abadi Photo Copy Centre",
        lat: -6.976581843358885,
        lon: 107.63477142054336,
        price: "Rp400 - Rp2000",
        minPrice: 400,
        busyHours: ["14:00", "17:00"],
        estimatedWait: 10,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "3 komputer ready",
        fasilitas: ["photocopy", "print ukuran A4", "menjual alat tulis kebutuhan sekolah"],
        jamBuka: "06:30",
        jamTutup: "21:00",
        onlineOrder: true,
        rating: 4.6
      },
      {
        name: "Toko Elvi Photo Copy",
        lat: -6.985952818387681,
        lon: 107.626274224299,
        price: "Rp100 - Rp5000",
        minPrice: 100,
        busyHours: ["15:00", "17:00"],
        estimatedWait: 10,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "3 komputer ready",
        fasilitas: ["alat tulis & kantor", "alat komputer", "ATK", "alat lukis", "kado", "kelengkapan sekolah", "sound system", "styrofoam", "perlengkapan ulang tahun", "souvenir & kado hantaran", "mika", "buku bacaan & bahan tulis", "buku sekolah", "kertas HVS", "alat listrik", "photocopy", "penjilidan", "laminating"],
        jamBuka: "08:30",
        jamTutup: "20:00",
        onlineOrder: true,
        rating: 4.2
      },
      {
        name: "Tunggal Photocopy",
        lat: -6.971670955002597,
        lon: 107.63346068944621,
        price: "Rp500 - Rp10000",
        minPrice: 500,
        busyHours: ["15:00", "18:00"],
        estimatedWait: 4,
        cocokUntuk: ["mahasiswa", "anak sekolah"],
        mesinStatus: "10 komputer ready",
        fasilitas: ["photocopy", "print berbagai ukuran kertas (A1, A2, A3, A4, A5)", "cetak spanduk", "jilid buku", "jilid ring", "laminating", "jual ATK", "art supply"],
        jamBuka: "06:00",
        jamTutup: "22:00",
        onlineOrder: true,
        rating: 4.7
      },
      {
        name: "Adhyaksa Print",
        lat: -6.966583913530798,
        lon: 107.63460695031763,
        price: "Rp200 - Rp7000",
        minPrice: 200,
        busyHours: ["14:00", "15:00"],
        estimatedWait: 15,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "1 komputer ready",
        fasilitas: ["photocopy", "print", "jual ATK"],
        jamBuka: "08:00",
        jamTutup: "21:00",
        onlineOrder: true,
        rating: 4.0
      },
      {
        name: "Ramah Foto Copy",
        lat: -6.982059679369543,
        lon: 107.6333171471077,
        price: "Rp500 - Rp5000",
        minPrice: 500,
        busyHours: ["16:00", "19:00"],
        estimatedWait: 5,
        cocokUntuk: ["mahasiswa", "pekerja kantoran"],
        mesinStatus: "6 komputer ready",
        fasilitas: ["photocopy", "print kerta (A4,A3)", "ATK"],
        jamBuka: "08:00",
        jamTutup: "22:00",
        onlineOrder: true,
        rating: 3.5
      },
      {
        name: "Putra Bersaudara Photocopy",
        lat: -6.966844320108848,
        lon: 107.63770113083594,
        price: "Rp300 - Rp5000",
        minPrice: 300,
        busyHours: ["16:00", "17:00"],
        estimatedWait: 20,
        cocokUntuk: ["mahasiswa", "pekerja kantoran"],
        mesinStatus: "1 komputer ready",
        fasilitas: ["photocopy", "print A4"],
        jamBuka: "07:00",
        jamTutup: "22:00",
        onlineOrder: true,
        rating: 1.0
      },
      {
        name: "Photo copy Print Internet Scan dan ATK",
        lat: -6.972475234810914,
        lon: 107.63595646836234,
        price: "Rp300 - Rp6000",
        minPrice: 300,
        busyHours: ["12:00", "14:00"],
        estimatedWait: 10,
        cocokUntuk: ["mahasiswa", "pekerja kantoran"],
        mesinStatus: "2 komputer ready",
        fasilitas: ["photocopy", "print", "jilid", "scan dokumen", "ATK"],
        jamBuka: "07:30",
        jamTutup: "21:00",
        onlineOrder: true,
        rating: 4.2
      },
      {
        name: "Fotocopy & PrintAjaDiSini",
        lat: -6.968985580206307,
        lon: 107.65355149421902,
        price: "Rp500 - Rp10000",
        minPrice: 500,
        busyHours: ["11:00", "13:00"],
        estimatedWait: 10,
        cocokUntuk: ["mahasiswa"],
        mesinStatus: "1 komputer ready",
        fasilitas: ["photocopy", "print (kertas, stiker, kalender)", "jilid", "scan", "ATK"],
        jamBuka: "08:00",
        jamTutup: "21:00",
        onlineOrder: true,
        rating: 5.0
      },
      {
        name: "AZKY copy center ATK dan print",
        lat: -6.971979310913202,
        lon: 107.64803168040041,
        price: "Rp1000 - Rp3000",
        minPrice: 1000,
        busyHours: ["14:00", "15:00"],
        estimatedWait: 10,
        cocokUntuk: ["mahasiswa", "anak sekolah"],
        mesinStatus: "1 komputer ready",
        fasilitas: ["photocopy", "print A4"],
        jamBuka: "06:30",
        jamTutup: "21:00",
        onlineOrder: true,
        rating: 4.9
      },
      {
        name: "Wulan photocopy",
        lat: -6.96931407918572,
        lon: 107.63698657139304,
        price: "Rp500 - Rp2000",
        minPrice: 500,
        busyHours: ["05.30:00", "08:00"],
        estimatedWait: 10,
        cocokUntuk: ["mahasiswa", "anak sekolah", "peker kantoran"],
        mesinStatus: "2 komputer ready",
        fasilitas: ["photocopy", "print A4", "ATK"],
        jamBuka: "09:00",
        jamTutup: "21:00",
        onlineOrder: false,
        rating: 3.7
      },
  
  ]; 

    const map = L.map('map').setView([-6.9718, 107.6334], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    L.control.locate({ position: 'topleft', strings: { title: "Lokasiku" }, drawCircle: true }).addTo(map);

    let routeControl = null;
    let userLocation = null;
    let radiusCircle = [];

    function showAllMarkers() {
    photocopyLocations.forEach(loc => {
      const marker = L.marker([loc.lat, loc.lon]).addTo(map);
      marker.bindPopup(`<strong>${loc.name}</strong><br>Harga: ${loc.price}`);
    });

    updateResultTable(photocopyLocations);
  }

  function updateResultTable(data) {
    const tbody = document.getElementById("resultTableBody");
    
  if (!tbody) {
    console.error("Element tbody not found!");
    return;
  }

  tbody.innerHTML = "";

  data.forEach(loc => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${loc.name}</td>
      <td>${loc.price}</td>
      <td>${loc.jamBuka} - ${loc.jamTutup}</td>
      <td>${loc.onlineOrder ? "Ya" : "Tidak"}</td>
      <td>${loc.rating ?? "-"}</td>
      <td>${loc.busyHours ? `${loc.busyHours[0]} - ${loc.busyHours[1]}` : "-"}</td>
      <td>${loc.estimatedWait ? loc.estimatedWait + " mnt" : "-"}</td>
      <td>${loc.mesinStatus ?? "-"}</td>
      <td>${Array.isArray(loc.cocokUntuk) ? loc.cocokUntuk.join(", ") : "-"}</td>
    `;

    // 🟡 Baris interaktif untuk klik -> lokasi di peta
    row.style.cursor = 'pointer';
    row.addEventListener('click', () => {
      map.setView([loc.lat, loc.lon], 17);
      L.popup()
        .setLatLng([loc.lat, loc.lon])
        .setContent(`<strong>${loc.name}</strong><br>Harga: ${loc.price}`)
        .openOn(map);
        if (userLocation) {
          if (routeControl) map.removeControl(routeControl);

          routeControl = L.Routing.control({
            waypoints: [
              L.latLng(userLocation.lat, userLocation.lng),
              L.latLng(loc.lat, loc.lon)
            ],
            routeWhileDragging: false,
            draggableWaypoints: false,
            addWaypoints: false,
            lineOptions: {
              styles: [{ color: '#FF1493', weight: 4 }]
            },
            createMarker: function (i, wp) {
              return i === 0
                ? L.marker(wp.latLng).bindPopup("Lokasi Anda")
                : L.marker(wp.latLng).bindPopup(`<strong>${loc.name}</strong>`);
            }
          }).addTo(map);
        } else {
          alert("Lokasi Anda belum terdeteksi. Klik 📍 Fotokopi Terdekat dulu.");
        }
      });

    tbody.appendChild(row);
  });
}


function showAllMarkers() {
  photocopyLocations.forEach(loc => {
    const marker = L.marker([loc.lat, loc.lon]).addTo(map);
    marker.bindPopup(`<strong>${loc.name}</strong><br>Harga: ${loc.price}`);
    // (marker click logic tetap seperti sebelumnya)
  });

  updateResultTable(photocopyLocations); // Ini WAJIB
}

    function showCheapest() {
      let cheapest = photocopyLocations[0];
      photocopyLocations.forEach(loc => {
        if (loc.minPrice < cheapest.minPrice) cheapest = loc;
      });
      map.setView([cheapest.lat, cheapest.lon], 17);
      L.popup().setLatLng([cheapest.lat, cheapest.lon]).setContent(`<strong>${cheapest.name}</strong><br>Harga: ${cheapest.price} <br><em>(Termurah)</em>`).openOn(map);
      updateResultTable([cheapest]);
    }

    function showWithinRadius() {
      const center = [-6.968930564277185, 107.62814627116805];
      const radiusValues = [500, 750, 1000, 1500, 2000, 2500, 3000];
      const colors = ['#FF0000', '#FF8000', '#FFFF00', '#00FF00', '#00FFFF', '#0080FF', '#8000FF'];

      // Hapus lingkaran sebelumnya jika ada
      if (radiusCircle.length) {
        radiusCircle.forEach(c => map.removeLayer(c));
      }

      radiusCircle = [];

      radiusValues.forEach((radius, i) => {
        const circle = L.circle(center, {
          color: colors[i],
          fillColor: colors[i],
          fillOpacity: 0.15,
          radius: radius
        }).addTo(map);
        radiusCircle.push(circle);
      });

      map.setView(center, 14);

      // Filter hasil hanya dalam radius terluar
      const maxRadius = radiusValues[radiusValues.length - 1];
      const inRadius = photocopyLocations.filter(loc => {
        return map.distance(center, [loc.lat, loc.lon]) <= maxRadius;
      });

      updateResultTable(inRadius);
    }

    function getNearest() {
      map.locate({ setView: true, maxZoom: 17 });
    }

    function findNearest(latlng, radius = 1000) {
      let nearest = null;
      let minDist = Infinity;
      photocopyLocations.forEach(loc => {
        const dist = map.distance(latlng, [loc.lat, loc.lon]);
        if (dist < radius && dist < minDist) {
          minDist = dist;
          nearest = { ...loc, distance: dist };
        }
      });
      return nearest;
    }

    map.on('locationfound', function (e) {
    userLocation = e.latlng; // <--- INI WAJIB supaya bisa buat rute!
    const nearest = findNearest(e.latlng);
      if (nearest) {
        if (routeControl) map.removeControl(routeControl);
        routeControl = L.Routing.control({
          waypoints: [
            L.latLng(e.latlng.lat, e.latlng.lng),
            L.latLng(nearest.lat, nearest.lon)
          ],
          routeWhileDragging: false,
          draggableWaypoints: false,
          addWaypoints: false,
          lineOptions: {
            styles: [{ color: '#3388ff', weight: 4 }]
          },
          createMarker: function (i, wp) {
            return i === 0
              ? L.marker(wp.latLng).bindPopup("Lokasi Anda").openPopup()
              : L.marker(wp.latLng).bindPopup(`<strong>${nearest.name}</strong><br>Jarak: ${Math.round(nearest.distance)} m`);
          }
        }).addTo(map);
      } else {
        L.popup().setLatLng(e.latlng).setContent("Tidak ada dalam radius 1 km").openOn(map);
      }
    });

    

  function routeFiltered() {
  const fasilitas = Array.from(document.querySelectorAll('.fasilitas:checked')).map(el => el.value);
  const jamMax = document.getElementById('jamBukaFilter').value;
  const online = document.getElementById('onlineFilter').value;
  const ratingMin = parseFloat(document.getElementById('ratingFilter').value);

  const filtered = photocopyLocations.filter(loc => {
    const bukaOk = loc.jamBuka <= jamMax;
    const onlineOk = (online === "") || (String(loc.onlineOrder) === online);
    const ratingOk = (loc.rating === null && ratingMin === 0) || (loc.rating !== null && loc.rating >= ratingMin);
    const fasilitasOk = fasilitas.every(f => loc.fasilitas.includes(f));
    return bukaOk && onlineOk && ratingOk && fasilitasOk;
  });

  if (filtered.length < 2) {
    alert("Minimal dua lokasi diperlukan untuk membuat rute.");
    return;
  }

  const waypoints = filtered.map(loc => L.latLng(loc.lat, loc.lon));

  if (routeControl) map.removeControl(routeControl);
  routeControl = L.Routing.control({
    waypoints: waypoints,
    routeWhileDragging: false,
    draggableWaypoints: false,
    addWaypoints: false,
    lineOptions: {
      styles: [{ color: '#FF69B4', weight: 4 }]
    },
    createMarker: function(i, wp) {
      const loc = filtered[i];
      return L.marker(wp).bindPopup(`<strong>${loc.name}</strong><br>Harga: ${loc.price}`).openPopup();
    }
  }).addTo(map);
}

  // Tetap sama, hanya pastikan elemen <datalist> bukan <select>
  searchBox.addEventListener('input', () => {
    const keyword = searchBox.value.toLowerCase();
    searchResults.innerHTML = '';

    const filtered = photocopyLocations.filter(loc =>
      loc.name.toLowerCase().includes(keyword)
    );

    filtered.forEach(loc => {
      const opt = document.createElement('option');
      opt.value = loc.name;
      opt.textContent = loc.name;
      searchResults.appendChild(opt);
    });

    updateResultTable(filtered); // tampilkan hasil di tabel
  });


    searchResults.addEventListener('change', () => {
      const selected = searchResults.value;
      const loc = photocopyLocations.find(p => p.name === selected);
      if (loc) {
        map.setView([loc.lat, loc.lon], 17);
        L.popup().setLatLng([loc.lat, loc.lon]).setContent(`<strong>${loc.name}</strong><br>Harga: ${loc.price}`).openOn(map);
      }
    });

    showAllMarkers();

  </script>
</body>
</html>
