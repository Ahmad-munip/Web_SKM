<?php
require_once('tcpdf/tcpdf.php');

// Koneksi ke database
$servername = "localhost:3307";
$username_db = "root"; 
$password_db = ""; 
$dbname = "bappeda_data";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}


// Fungsi untuk memformat tanggal dalam Bahasa Indonesia
function formatTanggal($date) {
    $hariArray = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulanArray = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $dayOfWeek = date('w', strtotime($date)); 
    $day = date('d', strtotime($date)); 
    $month = date('n', strtotime($date)) - 1; 
    $year = date('Y', strtotime($date)); 

    return $hariArray[$dayOfWeek] . ", " . $day . " " . $bulanArray[$month] . " " . $year . " " . date('H:i:s', strtotime($date)); 
}


// Proses pengunduhan data ke file CSV
if (isset($_GET['download'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="data_kepuasan_masyarakat.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Umur', 'Jenis Kelamin', 'Pekerjaan', 'Pendidikan', 'Nilai 1', 'Nilai 2', 'Nilai 3', 'Nilai 4', 'Nilai 5', 'Nilai 6', 'Nilai 7', 'Nilai 8', 'Nilai 9', 'Waktu'));

    $sql = "SELECT * FROM index_kepuasan_masyarakat";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $row['waktu'] = formatTanggal($row['waktu']);
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Proses download PDF
if (isset($_GET['download_pdf'])) {
    // Inisialisasi TCPDF dengan orientasi Landscape (L)
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    // Judul dokumen
    $pdf->Cell(0, 10, 'Data Kepuasan Masyarakat', 0, 1, 'C');

    // Mulai HTML tabel dengan kolom yang lebih lebar dan rata
    $html = '<table border="1" cellpadding="2" cellspacing="0" style="width:100%; text-align:center;">
                <thead>
                    <tr style="background-color:#1c81e6; color:#ffffff;">
                        <th width="4%">ID</th>
                        <th width="6%">Umur</th>
                        <th width="9%">Jenis Kelamin</th>
                        <th width="15%">Pekerjaan</th>
                        <th width="10%">Pendidikan</th>
                        <th width="4%">U1</th>
                        <th width="4%">U2</th>
                        <th width="4%">U3</th>
                        <th width="4%">U4</th>
                        <th width="4%">U5</th>
                        <th width="4%">U6</th>
                        <th width="4%">U7</th>
                        <th width="4%">U8</th>
                        <th width="4%">U9</th>
                        <th width="20%">Waktu</th>
                    </tr>
                </thead>
                <tbody>';

    // Ambil data dari database dan tambahkan ke tabel
    $sql = "SELECT * FROM index_kepuasan_masyarakat";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>
                    <td style="width: 4%">' . $row['id'] . '</td>
                    <td style="width: 6%">' . $row['umur'] . '</td>
                    <td style="width: 9%">' . $row['jenis_kelamin'] . '</td>
                    <td style="width: 15%">' . $row['pekerjaan'] . '</td>
                    <td style="width: 10%">' . $row['pendidikan'] . '</td>
                    <td style="width: 4%">' . $row['nilai_1'] . '</td>
                    <td style="width: 4%">' . $row['nilai_2'] . '</td>
                    <td style="width: 4%">' . $row['nilai_3'] . '</td>
                    <td style="width: 4%">' . $row['nilai_4'] . '</td>
                    <td style="width: 4%">' . $row['nilai_5'] . '</td>
                    <td style="width: 4%">' . $row['nilai_6'] . '</td>
                    <td style="width: 4%">' . $row['nilai_7'] . '</td>
                    <td style="width: 4%">' . $row['nilai_8'] . '</td>
                    <td style="width: 4%">' . $row['nilai_9'] . '</td>
                    <td style="width: 20%">' . formatTanggal($row['waktu']) . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';

    // Tambahkan HTML ke PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Output PDF
    $pdf->Output('data_kepuasan_masyarakat.pdf', 'I');
    exit();
}

// Ambil tahun dari input filter
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_quarter = isset($_GET['quarter']) ? (int)$_GET['quarter'] : '';


$filter_condition = "";
if ($selected_year) {
    // Jika ada tahun yang dipilih, tambahkan kondisi tahun pada filter
    $filter_condition .= "WHERE YEAR(waktu) = $selected_year";
}
if ($selected_quarter) {
    // Tentukan rentang bulan berdasarkan triwulan
    $quarter_condition = "";
    switch ($selected_quarter) {
        case 1:
            $quarter_condition = "MONTH(waktu) BETWEEN 1 AND 3";
            break;
        case 2:
            $quarter_condition = "MONTH(waktu) BETWEEN 4 AND 6";
            break;
        case 3:
            $quarter_condition = "MONTH(waktu) BETWEEN 7 AND 9";
            break;
        case 4:
            $quarter_condition = "MONTH(waktu) BETWEEN 10 AND 12";
            break;
    }

    if ($filter_condition) {
        $filter_condition .= " AND $quarter_condition";
    } else {
        $filter_condition = "WHERE $quarter_condition";
    }
}



// Hitung card dengan filter
$sql_count_filtered = "SELECT COUNT(*) AS total_responden FROM index_kepuasan_masyarakat $filter_condition";
$result_count_filtered = $conn->query($sql_count_filtered);
$row_count_filtered = $result_count_filtered->fetch_assoc();
$total_responden_filtered = $row_count_filtered['total_responden'];

// Hitung rata-rata performa layanan dan IKM dengan filter
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

// Hitung jumlah nilai dari U1 sampai U9 dengan filter
$sql_nilai_filtered = "SELECT nilai_1, nilai_2, nilai_3, nilai_4, nilai_5, nilai_6, nilai_7, nilai_8, nilai_9 
                       FROM index_kepuasan_masyarakat $filter_condition";
$result_nilai_filtered = $conn->query($sql_nilai_filtered);

// Tambahkan kode perhitungan di sini
$jumlah_nilai_total_filtered = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

if ($result_nilai_filtered->num_rows > 0) {
    while ($row_nilai_filtered = $result_nilai_filtered->fetch_assoc()) {
        for ($i = 1; $i <= 9; $i++) {
            if (isset($row_nilai_filtered['nilai_' . $i])) {
                $nilai = $row_nilai_filtered['nilai_' . $i];
                if ($nilai >= 1 && $nilai <= 4) {
                    $jumlah_nilai_total_filtered[$nilai]++;
                }
            }
        }
    }
} else {
    $jumlah_nilai_total_filtered = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
}
$total_baik = $jumlah_nilai_total_filtered[2] + $jumlah_nilai_total_filtered[3] + $jumlah_nilai_total_filtered[4];
$total_buruk = $jumlah_nilai_total_filtered[1];



$jumlah_nilai_total_filtered = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
while ($row_nilai_filtered = $result_nilai_filtered->fetch_assoc()) {
    for ($i = 1; $i <= 9; $i++) {
        $nilai = $row_nilai_filtered['nilai_' . $i];
        if ($nilai >= 1 && $nilai <= 4) {
            $jumlah_nilai_total_filtered[$nilai]++;
        }
    }
}

// Menghitung nilai rata-rata performa layanan dan IKM tanpa filter
$sql_avg_all = "SELECT AVG(nilai_1) AS avg_nilai_1, AVG(nilai_2) AS avg_nilai_2, AVG(nilai_3) AS avg_nilai_3,
                AVG(nilai_4) AS avg_nilai_4, AVG(nilai_5) AS avg_nilai_5, AVG(nilai_6) AS avg_nilai_6,
                AVG(nilai_7) AS avg_nilai_7, AVG(nilai_8) AS avg_nilai_8, AVG(nilai_9) AS avg_nilai_9
                FROM index_kepuasan_masyarakat";
$result_avg_all = $conn->query($sql_avg_all);
$row_avg_all = $result_avg_all->fetch_assoc();

$total_rata_rata_kali_0_11_all = array_sum(array_slice($row_avg_all, 0, 9)) * 0.11;
$total_rata_rata_kali_25_all = $total_rata_rata_kali_0_11_all * 25;

// Mengambil data untuk tabel dengan filter tahun
$sql_tabel = "SELECT 
            MONTH(waktu) AS bulan, 
            COUNT(*) AS total_responden, 
            SUM(nilai_1) AS total_nilai_1, 
            SUM(nilai_2) AS total_nilai_2,
            SUM(nilai_3) AS total_nilai_3,
            SUM(nilai_4) AS total_nilai_4,
            SUM(nilai_5) AS total_nilai_5,
            SUM(nilai_6) AS total_nilai_6,
            SUM(nilai_7) AS total_nilai_7,
            SUM(nilai_8) AS total_nilai_8,
            SUM(nilai_9) AS total_nilai_9,
            SUM(nilai_1 + nilai_2 + nilai_3 + nilai_4 + nilai_5 + nilai_6 + nilai_7 + nilai_8 + nilai_9) AS total_semua_nilai,
            (SUM(nilai_1 + nilai_2 + nilai_3 + nilai_4 + nilai_5 + nilai_6 + nilai_7 + nilai_8 + nilai_9) / COUNT(*)) * 0.11 AS nnr_per_responden_kali_0_11,
            ((SUM(nilai_1 + nilai_2 + nilai_3 + nilai_4 + nilai_5 + nilai_6 + nilai_7 + nilai_8 + nilai_9) / COUNT(*)) * 0.11) * 25 AS nilai_rata_rata_kali_25
        FROM index_kepuasan_masyarakat 
        $filter_condition
        GROUP BY MONTH(waktu) 
        ORDER BY MONTH(waktu)";


$result_tabel = $conn->query($sql_tabel);

$total_jumlah_nilai_filtered = array_sum($jumlah_nilai_total_filtered);

// Hitung penilaian baik dan buruk
$jumlah_penilaian_baik = array_fill(1, 9, 0); // Default nilai nol untuk semua indikator
$jumlah_penilaian_buruk = array_fill(1, 9, 0); // Default nilai nol untuk semua indikator

// while ($row_nilai_filtered = $result_nilai_filtered->fetch_assoc()) {
//     for ($i = 1; $i <= 9; $i++) {
//         if (isset($row_nilai_filtered["nilai_$i"])) {
//             $nilai = $row_nilai_filtered["nilai_$i"];
//             if ($nilai >= 2 && $nilai <= 4) { // Penilaian baik
//                 $jumlah_penilaian_baik[$i]++;
//             } elseif ($nilai == 1) { // Penilaian buruk
//                 $jumlah_penilaian_buruk[$i]++;
//             }
//         }
//     }
// }


// fungsi penilaian baik dan buruk
$sql_penilaian = "SELECT * FROM index_kepuasan_masyarakat $filter_condition";
$result_penilaian = $conn->query($sql_penilaian);

while ($row_penilaian = $result_penilaian->fetch_assoc()) {
    for ($i = 1; $i <= 9; $i++) {
        if ($row_penilaian['nilai_' . $i] >= 2) {
            $jumlah_penilaian_baik[$i]++;
        } elseif ($row_penilaian['nilai_' . $i] == 1) {
            $jumlah_penilaian_buruk[$i]++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kepuasan Masyarakat <?php echo $selected_year; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/3d.js"></script>
    <link rel="stylesheet" href="css/dashboard.css">

</head>
<body>

<div class="container mt-5 pt-5" data-aos="fade-up">
    <h1 class="text-center mb-5">Data Kepuasan Masyarakat <?php echo $selected_year; ?></h1>

    <!-- Form Filter -->
    <form method="get" action="" class="mb-5">
        <div class="row justify-content-center align-items-center">
            <!-- Dropdown Tahun -->
            <div class="col-md-3">
                <select name="year" class="form-select form-select-lg" aria-label="Tahun" data-aos="zoom-in">
                    <option value="">Semua Tahun</option>
                    <?php for ($year = 2023; $year <= date("Y"); $year++): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Dropdown Triwulan -->
            <div class="col-md-3">
                <select name="quarter" class="form-select form-select-lg" aria-label="Triwulan" data-aos="zoom-in">
                    <option value="">Semua Triwulan</option>
                    <option value="1" <?php echo (isset($_GET['quarter']) && $_GET['quarter'] == '1') ? 'selected' : ''; ?>>Triwulan 1</option>
                    <option value="2" <?php echo (isset($_GET['quarter']) && $_GET['quarter'] == '2') ? 'selected' : ''; ?>>Triwulan 2</option>
                    <option value="3" <?php echo (isset($_GET['quarter']) && $_GET['quarter'] == '3') ? 'selected' : ''; ?>>Triwulan 3</option>
                    <option value="4" <?php echo (isset($_GET['quarter']) && $_GET['quarter'] == '4') ? 'selected' : ''; ?>>Triwulan 4</option>
                </select>
            </div>

            <!-- Tombol Filter -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-success" name="filter" data-aos="zoom-in">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>

            <!-- Tombol Download Data dan Download PDF -->
            <div class="col-md-3 text-end" data-aos="zoom-in">
                <button type="submit" name="download" class="btn btn-primary me-2">
                    <i class="fas fa-download"></i> Unduh Data
                </button>
                <button type="submit" name="download_pdf" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
            </div>
        </div>
    </form>
</div>

    <!-- Main Cards -->
    <div class="container">
    <div class="row">
    <!-- Card Total Responden -->
        <div class="col-md-4 mb-4" data-aos="fade-right">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-users"></i> Total Responden</h5>
                    <p class="card-text display-4"><?php echo $total_responden_filtered; ?></p>
                </div>
            </div>
        </div>

         <!-- Card Performa Layanan -->
         <div class="col-md-4 mb-4" data-aos="fade-up">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-star"></i> Performa Layanan</h5>
                    <p class="card-text display-4"><?php echo round($total_rata_rata_kali_0_11_filtered, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Card IKM -->
        <div class="col-md-4 mb-4" data-aos="fade-left">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-smile"></i> IKM</h5>
                    <p class="card-text display-4"><?php echo round($total_rata_rata_kali_25_filtered, 2); ?></p>
                </div>
            </div>
        </div>
    </div>

  <!-- Progress Bars dan Chart.js IKM -->
  <div class="row">
 <!-- Progress Bars -->
<div class="col-md-4 mb-5" data-aos="zoom-in">
    <div class="card">
        <div class="card-header">
            <h5>Monitoring</h5>
        </div>
        <div class="card-body">
            <?php foreach ($jumlah_nilai_total_filtered as $nilai => $nnr): ?>
                <?php 
                $persentase = $total_jumlah_nilai_filtered > 0 ? round(($nnr / $total_jumlah_nilai_filtered) * 100, 2) : 0; 

                // Atur emoticon berdasarkan persentase
                if ($persentase < 20) {
                    $emoticon = "😢"; // Emoticon sedih
                } elseif ($persentase < 40) {
                    $emoticon = "😟"; // Emoticon tidak puas
                } elseif ($persentase < 60) {
                    $emoticon = "🙂"; // Emoticon netral
                } elseif ($persentase < 80) {
                    $emoticon = "😃"; // Emoticon cukup puas
                } else {
                    $emoticon = "😄"; // Emoticon bahagia
                }

                // Tentukan warna progress bar berdasarkan persentase
                if ($persentase < 20) {
                    $progressColor = 'bg-danger'; // Merah
                } elseif ($persentase < 40) {
                    $progressColor = 'bg-warning'; // Oranye
                } elseif ($persentase < 60) {
                    $progressColor = 'bg-warning text-dark'; // Kuning
                } elseif ($persentase < 80) {
                    $progressColor = 'bg-success'; // Hijau Muda
                } else {
                    $progressColor = 'bg-success'; // Hijau
                }
                ?>

                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <span style="font-size: 2rem;"><?php echo $emoticon; ?></span> <!-- Tampilkan emoticon -->
                    <span><?php echo $persentase; ?>%</span>
                    <span><?php echo $nnr; ?></span>
                </div>
                <div class="progress mb-3">
                    <div class="progress-bar <?php echo $progressColor; ?>" role="progressbar" style="width: <?php echo $persentase; ?>%;" aria-valuenow="<?php echo $persentase; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


    <!-- Chart.js IKM -->
    <div class="col-md-8 mb-5" data-aos="zoom-in">
    <div class="card">
        <div class="card-header">
            <h5>Tingkat Kepuasan</h5>
        </div>
        <div class="card-body">
            <div id="ikmChart" style="width: 100%; height: 400px;"></div>
        </div>
    </div>
    </div>
    
    <!-- card penilaian -->
<div class="penilaian-container">
    <div class="penilaian-card baik">
        <h3>Penilaian yang dinilai Baik</h3>
        <?php
        $keterangan = [
            1 => "Kesesuaian Persyaratan Pelayanan dgn Jenis Pelayanan",
            2 => "Pemahaman terhadap kemudahan prosedur pelayanan",
            3 => "Kecepatan waktu dalam memberikan pelayanan",
            4 => "Kewajaran Biaya/tarif",
            5 => "Kesesuaian produk layanan antara yang tercantum dalam standar pelayanan dengan hasil yang diberikan",
            6 => "Kompetensi kemampuan petugas",
            7 => "Perilaku petugas dalam pelayanan",
            8 => "Kualitas Sarana dan Prasarana",
            9 => "Penanganan Pegaduan Pengguna Layanan"
        ];
        if (array_sum($jumlah_penilaian_baik) > 0): ?>
            <?php for ($i = 1; $i <= 9; $i++): ?>
                <div class="penilaian-item">
            <span><?php echo $keterangan[$i]; ?> (<?php echo $jumlah_penilaian_baik[$i]; ?>)</span>
            <div class="penilaian-progress-bar penilaian-progress-bar-baik">
                <div class="penilaian-progress" style="width: <?php 
                    $max_baik = max($jumlah_penilaian_baik) > 0 ? max($jumlah_penilaian_baik) : 1; // Hindari pembagian nol
                    echo ($jumlah_penilaian_baik[$i] / $max_baik) * 100; 
                ?>%;"></div>
            </div>
        </div>
    <?php endfor; ?>
<?php else: ?>
    <p>Tidak ada data penilaian baik untuk periode ini.</p>
<?php endif; ?>

    </div>

    
    <div class="penilaian-card kurang">
        <h3>Penilaian yang dinilai Kurang</h3>
        <?php if (array_sum($jumlah_penilaian_buruk) > 0): ?>
    <?php for ($i = 1; $i <= 9; $i++): ?>
        <div class="penilaian-item">
            <span><?php echo $keterangan[$i]; ?> (<?php echo $jumlah_penilaian_buruk[$i]; ?>)</span>
            <div class="penilaian-progress-bar penilaian-progress-bar-kurang">
                <div class="penilaian-progress" style="width: <?php 
                    $max_buruk = max($jumlah_penilaian_buruk) > 0 ? max($jumlah_penilaian_buruk) : 1; // Hindari pembagian nol
                    echo ($jumlah_penilaian_buruk[$i] / $max_buruk) * 100; 
                ?>%;"></div>
            </div>
        </div>
    <?php endfor; ?>
<?php else: ?>
    <p>Tidak ada data penilaian buruk untuk periode ini.</p>
<?php endif; ?>

    </div>
</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // AOS Animation initialization
    AOS.init();

     // Data bulan dalam bahasa Indonesia
     const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// Data nilai IKM, default 0 untuk bulan tanpa data
const nilaiIKM = new Array(12).fill(0); // Mengisi nilai 0 untuk semua bulan

// Isi dengan data dari PHP
<?php if ($result_tabel->num_rows > 0): ?>
    <?php while($row = $result_tabel->fetch_assoc()): ?>
        nilaiIKM[<?php echo $row['bulan'] - 1; ?>] = <?php echo round($row['nilai_rata_rata_kali_25'], 2); ?>;
    <?php endwhile; ?>
<?php endif; ?>

// Fungsi untuk mendapatkan warna berdasarkan nilai
function getColor(value) {
    const max = 100; // Asumsi nilai maksimum
    const min = 0;   // Asumsi nilai minimum
    const ratio = (value - min) / (max - min);
    const red = Math.floor(255 * (1 - ratio)); // Merah akan berkurang saat nilai meningkat
    const green = Math.floor(255 * ratio);     // Hijau akan meningkat saat nilai meningkat
    return `rgb(${red}, ${green}, 0)`;
}

// Menggambar chart Highcharts
Highcharts.chart('ikmChart', {
    chart: {
        type: 'column',
        options3d: {
            enabled: true,
            alpha: 10,
            beta: 10,
            depth: 50,
            viewDistance: 25
        }
    },
    title: {
        text: 'Tingkat Kepuasan Masyarakat'
    },
    xAxis: {
        categories: bulan,
        title: {
            text: 'Bulan'
        }
    },
    yAxis: {
        min: 0,
        max: 100, // Set nilai maksimum untuk y-axis
        title: {
            text: 'Nilai IKM'
        }
    },
    series: [{
        name: 'IKM',
        data: nilaiIKM.map(value => ({
            y: value,
            color: getColor(value) // Menggunakan fungsi untuk mendapatkan warna
        })),
        dataLabels: {
            enabled: true,
            format: '{point.y}'
        }
    }],
    plotOptions: {
        column: {
            depth: 25,
            dataLabels: {
                enabled: true
            },
            grouping: false // Memungkinkan penampilan balok yang lebih panjang
        }
    }
});
</script>
</body>
</html>
