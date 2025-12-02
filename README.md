# 🌾 Sistem Pendukung Keputusan Pemilihan Bibit Padi  
### Menggunakan Metode AHP & TOPSIS

Website ini dibuat untuk membantu kelompok tani menentukan bibit padi berkualitas berdasarkan beberapa kriteria penting. Sistem menggunakan metode **AHP** untuk menghitung bobot kriteria dan **TOPSIS** untuk menentukan ranking alternatif.

---

## 📖 Deskripsi Proyek
Proyek ini merupakan aplikasi **Sistem Pendukung Keputusan (SPK)** berbasis web. Aplikasi ini mempermudah proses seleksi bibit padi terbaik dengan menggunakan gabungan metode:

- **AHP (Analytical Hierarchy Process)** → menentukan bobot prioritas kriteria  
- **TOPSIS (Technique for Order Preference by Similarity to Ideal Solution)** → menghitung jarak D⁺, D⁻, nilai preferensi & ranking

Hasil akhir berupa **bibit padi terbaik** yang direkomendasikan berdasarkan nilai tertinggi.

---

## 🚀 Fitur Utama
- Manajemen Alternatif (Bibit Padi)
- Manajemen Kriteria & Bobot
- Input Nilai Kecocokan (1–5)
- Perhitungan:
  - Normalisasi matriks
  - Normalisasi terbobot
  - Solusi ideal positif & negatif
  - Jarak D⁺ dan D⁻
  - Nilai preferensi (V)
- Hasil Perankingan otomatis
- Dashboard hasil keputusan

---

## 🏗 Teknologi yang Digunakan
- **PHP**
- **MySQL**
- **HTML/CSS**
- **Bootstrap**
- **JavaScript**
- **XAMPP**

---

## 📂 Struktur Folder
``` tree
project/
├── index.php
├── koneksi.php
├── config.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── img/
│       └── (gambar pendukung)
│
├── pages/
│   ├── alternatif.php
│   ├── kriteria.php
│   ├── nilai.php
│   └── hasil.php
│
├── process/
│   ├── ahp.php
│   └── topsis.php
│
└── topsis.sql
```

## 🗄 Struktur Database
Proyek menggunakan empat tabel utama:

### 1️⃣ alternatif
Menyimpan data bibit padi.

### 2️⃣ kriteria
Menyimpan data kriteria beserta bobot AHP dan sifat benefit/cost.

### 3️⃣ nilai_alternatif
Menyimpan rating kecocokan alternatif pada setiap kriteria (skala 1–5).

### 4️⃣ hasil_topsis
Menyimpan hasil perhitungan TOPSIS:
- D+
- D−
- nilai preferensi
- ranking

SQL lengkap tersedia dalam file `topsis.sql`.

---

## ⚙️ Cara Instalasi

### 1. Clone repository
```bash
git clone https://github.com/username/nama-project.git
```

### 2. Masuk ke folder project
```bash
cd nama-project
```

### 3. Import database
```bash
Buat database: topsis_db
Buka phpMyAdmin
```

### 4. Sesuaikan konfigurasi database
```bash
Buka koneksi.php:
$host = "localhost";
$user = "root";
$pass = "";
$db   = "topsis_db";
```

### 5. Jalankan di browser
```bash
http://localhost/nama-project/
```

# ✨ Author
Rizki Pratama Sunarko(240411100181) <br>
Pengembang Sistem Pendukung Keputusan <br>
Metode AHP & TOPSIS – Pemilihan Bibit Padi
