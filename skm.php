<?php

$servername = "localhost:3307";
$username_db = "root";
$password_db = "";
$dbname = "bappeda_data";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Hitung triwulan dan tahun saat ini
$current_year = date('Y');
$current_month = date('n');
$current_quarter = ceil($current_month / 3);

// Tentukan triwulan sebelumnya
$previous_quarter = $current_quarter - 1;
if ($previous_quarter < 1) {
    $previous_quarter = 4;
    $current_year--;  
}

// Ambil tahun dan triwulan dari input filter (atau gunakan triwulan sebelumnya sebagai default)
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;
$selected_quarter = isset($_GET['quarter']) ? (int)$_GET['quarter'] : $previous_quarter;

// Kondisi filter default
$filter_condition = "WHERE YEAR(waktu) = $selected_year";
if (!empty($selected_quarter)) {
    switch ($selected_quarter) {
        case 1:
            $filter_condition .= " AND MONTH(waktu) BETWEEN 1 AND 3";
            break;
        case 2:
            $filter_condition .= " AND MONTH(waktu) BETWEEN 4 AND 6";
            break;
        case 3:
            $filter_condition .= " AND MONTH(waktu) BETWEEN 7 AND 9";
            break;
        case 4:
            $filter_condition .= " AND MONTH(waktu) BETWEEN 10 AND 12";
            break;
    }
}

// Hitung nilai IKM berdasarkan filter
$sql_avg_filtered = "SELECT 
    AVG(nilai_1) AS avg_nilai_1, 
    AVG(nilai_2) AS avg_nilai_2, 
    AVG(nilai_3) AS avg_nilai_3,
    AVG(nilai_4) AS avg_nilai_4, 
    AVG(nilai_5) AS avg_nilai_5, 
    AVG(nilai_6) AS avg_nilai_6,
    AVG(nilai_7) AS avg_nilai_7, 
    AVG(nilai_8) AS avg_nilai_8, 
    AVG(nilai_9) AS avg_nilai_9
    FROM index_kepuasan_masyarakat $filter_condition";

$result_avg_filtered = $conn->query($sql_avg_filtered);
$row_avg_filtered = $result_avg_filtered->fetch_assoc();

$total_rata_rata_kali_0_11_filtered = array_sum(array_slice($row_avg_filtered, 0, 9)) * 0.11;
$total_rata_rata_kali_25_filtered = $total_rata_rata_kali_0_11_filtered * 25;

// Start a session to store IKM value
session_start();
$_SESSION['ikm_value'] = round($total_rata_rata_kali_25_filtered, 2);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKM</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/skm.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Mengatur font default untuk semua elemen */
        body {
            font-family: 'Rubik', sans-serif;
    }
</style>
</head> 
<body>
<nav>
    <div class="layar-dalam">
        <!-- Logo di kiri -->
        <div class="logo" data-aos="flip-right">
            <a href="#"><img src="img/BAPPEDA logo.png" alt="BAPPEDA Logo"></a>
        </div>

        <!-- Nilai IKM di tengah -->
        <div class="ikm" data-aos="fade-left">
            <h1 class="card-title text-xl font-semibold flex items-center justify-center">
                <i class="fa-solid fa-chart-line text-2xl"></i> IKM
            </h1>
            <div class="card-text">
                <?php
                // Nilai IKM yang sudah dihitung dengan rumus terbaru
                $ikm_value = $_SESSION['ikm_value']; // Ganti dengan perhitungan dari session
                $ikm_value_formatted = number_format($ikm_value, 2);

                // Tentukan warna berdasarkan nilai IKM
                if ($ikm_value >= 0 && $ikm_value <= 50) {
                    $color = "red"; // Merah
                } elseif ($ikm_value >= 51 && $ikm_value <= 75) {
                    $color = "yellow"; // Kuning
                } elseif ($ikm_value >= 76 && $ikm_value <= 87) {
                    $color = "green"; // Hijau
                } elseif ($ikm_value >= 88 && $ikm_value <= 100) {
                    $color = "blue"; // Biru
                } else {
                    $color = "black"; // Nilai di luar rentang (tidak valid)
                }

                // Tampilkan hasil
                echo "<p><span style='color: $color; font-weight: bold; font-size: 30px;'>$ikm_value_formatted</span></p>";
                ?>
            </div>
        </div>

        <!-- SIIKUAT di kanan -->
        <div class="pojok">
            <span class="material-symbols-outlined">passkey</span>
            <a href="login.php">SIIKUAT</a>
        </div>
    </div>
</nav>

    <header>
        <div class="intro">
            
            <h3 data-aos="fade-down" data-aos-duration="2000">SISTEM INFORMASI INDEKS <br> KEPUASAN MASYARAKAT  </h3>
            <br><br>
            <!-- <h3 data-aos="fade-down" data-aos-duration="2000"> Masyarakat Terhadap Pelayanan Publik </h3>  -->

            <!-- <h3 class="skt" data-aos="fade-down" data-aos-duration="2000">SIIKUAT</h3> <br><br> -->
            </div>
            
        <div class="container">
    <div class="box" data-aos="fade-right" data-aos-duration="2000">
        <a href="progress.php"><img src="img/hasile.png" alt=""></a>
        <p>
            <a href="progress.php" class="tombol" target="blank">Hasil Kuisioner</a>
        </p>
    </div>  
    <div class="box" data-aos="fade-left" data-aos-duration="2000">
        <div class="boxer">
        <a href="form.php"><img src="img/ir.jpg" alt=""></a>
        <p class="ja">
            <a href="form.php" class="tombol">Isi Kuisioner</a>
        </p>
        </div>
      
    </div>
    
     <!-- <div>
        <img src="img/bg.jpg">
    </div> -->
   
</div>
</header>
<main>
        <div class="definisi" data-aos="fade-up" data-aos-duration="2000"><br>
            <h1>
            apa itu SIIKUAT?</h1>
        </div>
        
        <div class="content-wrapper">
            <div class="text-content" data-aos="fade-right" data-aos-duration="2000">
                <p>SIIKUAT adalah Sistem Informasi Indeks Kepuasan Masyarakat yang digunakan untuk menghitung hasil Survei Kepuasan Masyarakat secara cepat, efektif, efisien serta realtime dan dapat  menyimpan data hasil perhitungan Indeks Kepuasan Masyarakat dengan lebih aman.</p>
            </div>
            <div class="logo-baru" data-aos="fade-left" data-aos-duration="2500">
                <img src="img/1.png" alt="">
            </div>
        </div>
        
    </main>
    <footer id="Kontak">
    <div class="layar-dalam">
        <div class="footer-row">
            <div data-aos="fade-right" data-aos-duration="2000" class="kontak">
                <h5>Link Terkait</h5>
                <a href="https://bappeda.grobogan.go.id/">bappeda.grobogan.go.id</a>
            </div>

            <div data-aos="fade-left" data-aos-duration="2000" class="kontak">
                <h5>Kontak</h5>
                Jl.SParmanNo.23,Brambangan,Purwodadi
                <a href="https://www.instagram.com/bappeda.grobogan?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" 
                   target="_blank" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>bappeda.grobogan
                </a><br>
                <a href="https://www.youtube.com/@bappedakabupatengrobogan4759" 
                   target="_blank" aria-label="YouTube">
                    <i class="fab fa-youtube"></i>bappeda.grobogan
                </a><br>
                <a href="https://web.facebook.com/bappeda.grobogan.56" target="_blank" aria-label="Facebook">
                    <i class="fab fa-facebook"></i>bappeda.grobogan
                </a>
            </div>
        </div>
    </div>
</footer>


    <script src="https://code.jquery.com/jquery-3.7.1.js" crossorigin="anonymous"></script>
    <script src="javascript.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init();
    </script>
</body>
</html>
