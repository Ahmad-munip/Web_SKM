<?php
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

// Ambil tahun dari input filter
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Proses filter untuk tabel berdasarkan tahun
$filter_condition = "";
if ($selected_year >= 2023) {
    $filter_condition = "WHERE YEAR(waktu) = $selected_year";
}

// Hitung total responden tanpa filter
$sql_count_all = "SELECT COUNT(*) AS total_responden FROM index_kepuasan_masyarakat";
$result_count_all = $conn->query($sql_count_all);
$row_count_all = $result_count_all->fetch_assoc();
$total_responden_all = $row_count_all['total_responden'];

// Hitung jumlah nilai dari U1 sampai U9 tanpa filter
$sql_nilai_all = "SELECT nilai_1, nilai_2, nilai_3, nilai_4, nilai_5, nilai_6, nilai_7, nilai_8, nilai_9 FROM index_kepuasan_masyarakat";
$result_nilai_all = $conn->query($sql_nilai_all);

$jumlah_nilai_total_all = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
while ($row_nilai_all = $result_nilai_all->fetch_assoc()) {
    for ($i = 1; $i <= 9; $i++) {
        $nilai = $row_nilai_all['nilai_' . $i];
        if ($nilai >= 1 && $nilai <= 4) {
            $jumlah_nilai_total_all[$nilai]++;
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

$total_jumlah_nilai_all = array_sum($jumlah_nilai_total_all);


// Hitung jumlah penilaian baik dan buruk dari U1 sampai U9
$penilaian_baik = 0;
$penilaian_buruk = 0;

// Ambil nilai dari U1 sampai U9 dan hitung penilaian baik dan buruk
$sql_nilai = "SELECT nilai_1, nilai_2, nilai_3, nilai_4, nilai_5, nilai_6, nilai_7, nilai_8, nilai_9 FROM index_kepuasan_masyarakat";
$result_nilai = $conn->query($sql_nilai);

while ($row_nilai = $result_nilai->fetch_assoc()) {
    for ($i = 1; $i <= 9; $i++) {
        $nilai = $row_nilai['nilai_' . $i];
        if ($nilai >= 2 && $nilai <= 4) { // Penilaian baik
            $penilaian_baik++;
        } elseif ($nilai == 1) { // Penilaian buruk
            $penilaian_buruk++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kepuasan Masyarakat Tahun <?php echo $selected_year; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center">Data Kepuasan Masyarakat Tahun <?php echo $selected_year; ?></h1>

    <!-- Form Filter -->
    <form method="get" action="" class="mb-4">
        <div class="row mb-3">
            <div class="col">
                <select name="year" class="form-select" aria-label="Tahun">
                    <option value="">Pilih Tahun</option>
                    <?php for ($year = 2023; $year <= date("Y"); $year++): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Row untuk Card -->
    <div class="row">
        <!-- Card Total Responden -->
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5>Total Responden</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo $total_responden_all; ?></h6>
                </div>
            </div>
        </div>

        <!-- Card Performa Layanan -->
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5>Performa Layanan (Semua Tahun)</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo round($total_rata_rata_kali_0_11_all, 2); ?></h6>
                </div>
            </div>
        </div>

        <!-- Card Indeks Kepuasan Masyarakat -->
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5>Indeks Kepuasan Masyarakat (Semua Tahun)</h5>
                </div>
                <div class="card-body">
                    <h6><?php echo round($total_rata_rata_kali_25_all, 2); ?></h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Progress -->
    <div class="card mt-3">
        <div class="card-header">
            <h5>Progress (Semua Tahun)</h5>
        </div>
        <div class="card-body">
            <?php foreach ($jumlah_nilai_total_all as $nilai => $nnr): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Nilai <?php echo $nilai; ?></span>
                        <span><?php echo $nnr; ?> (<?php echo $total_jumlah_nilai_all > 0 ? round(($nnr / $total_jumlah_nilai_all) * 100, 2) : 0; ?>%)</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $total_jumlah_nilai_all > 0 ? round(($nnr / $total_jumlah_nilai_all) * 100, 2) : 0; ?>%;" aria-valuenow="<?php echo $total_jumlah_nilai_all > 0 ? round(($nnr / $total_jumlah_nilai_all) * 100, 2) : 0; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<!-- Row untuk Card Penilaian Baik dan Penilaian Buruk -->
<div class="row mt-3">
    <!-- Card Penilaian Baik -->
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5>Penilaian Baik</h5>
            </div>
            <div class="card-body">
                <h6><?php echo $penilaian_baik; ?></h6>
            </div>
        </div>
    </div>

    <!-- Card Penilaian Buruk -->
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5>Penilaian Buruk</h5>
            </div>
            <div class="card-body">
                <h6><?php echo $penilaian_buruk; ?></h6>
            </div>
        </div>
    </div>
</div>

    <!-- Card Tabel Data -->
    <div class="card mt-3">
        <div class="card-header">
            <h5>Data IKM</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Nilai IKM</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result_tabel->num_rows > 0): ?>
                    <?php while($row = $result_tabel->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date("F", mktime(0, 0, 0, $row['bulan'], 1)); ?></td>
                            <td><?php echo round($row['nilai_rata_rata_kali_25'], 2); ?></td> <!-- Tampilkan nilai IKM -->
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">Tidak ada data ditemukan untuk tahun ini.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
