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

// Hitung penilaian baik dan buruk
$jumlah_penilaian_baik = array_fill(1, 9, 0);
$jumlah_penilaian_buruk = array_fill(1, 9, 0);

// Mengambil data untuk penilaian baik dan buruk
$sql_penilaian = "SELECT * FROM index_kepuasan_masyarakat $filter_condition";
$result_penilaian = $conn->query($sql_penilaian);

while ($row_penilaian = $result_penilaian->fetch_assoc()) {
    for ($i = 1; $i <= 9; $i++) {
        if ($row_penilaian['nilai_' . $i] >= 3) {
            $jumlah_penilaian_baik[$i]++;
        } elseif ($row_penilaian['nilai_' . $i] <= 2) {
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
    <title>Data Kepuasan Masyarakat Tahun <?php echo $selected_year; ?></title>
    <link rel="stylesheet" href="final.css">
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
        for ($i = 1; $i <= 9; $i++): ?>
            <div class="penilaian-item">
                <span><?php echo $keterangan[$i]; ?> (<?php echo $jumlah_penilaian_baik[$i]; ?>)</span>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo ($jumlah_penilaian_baik[$i] / max($jumlah_penilaian_baik)) * 100; ?>%;"></div>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <div class="penilaian-card kurang">
        <h3>Penilaian yang dinilai Kurang</h3>
        <?php for ($i = 1; $i <= 9; $i++): ?>
            <div class="penilaian-item">
                <span><?php echo $keterangan[$i]; ?> (<?php echo $jumlah_penilaian_buruk[$i]; ?>)</span>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo ($jumlah_penilaian_buruk[$i] / max($jumlah_penilaian_buruk)) * 100; ?>%; background-color: #e74c3c;"></div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>


   

    <!-- Tampilkan Progress Bar -->
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

    <!-- Tabel Data -->
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Responden</th>
                <th>Total Nilai U1</th>
                <th>Total Nilai U2</th>
                <th>Total Nilai U3</th>
                <th>Total Nilai U4</th>
                <th>Total Nilai U5</th>
                <th>Total Nilai U6</th>
                <th>Total Nilai U7</th>
                <th>Total Nilai U8</th>
                <th>Total Nilai U9</th>
                <th>Total NNR</th>
                <th>Nilai Rata-rata</th>
                <th>Nilai IKM</th> <!-- Kolom baru -->
            </tr>
        </thead>
        <tbody>
        <?php if ($result_tabel->num_rows > 0): ?>
            <?php while($row = $result_tabel->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date("F", mktime(0, 0, 0, $row['bulan'], 1)); ?></td>
                    <td><?php echo $row['total_responden']; ?></td>
                    <td><?php echo $row['total_nilai_1']; ?></td>
                    <td><?php echo $row['total_nilai_2']; ?></td>
                    <td><?php echo $row['total_nilai_3']; ?></td>
                    <td><?php echo $row['total_nilai_4']; ?></td>
                    <td><?php echo $row['total_nilai_5']; ?></td>
                    <td><?php echo $row['total_nilai_6']; ?></td>
                    <td><?php echo $row['total_nilai_7']; ?></td>
                    <td><?php echo $row['total_nilai_8']; ?></td>
                    <td><?php echo $row['total_nilai_9']; ?></td>
                    <td><?php echo $row['total_semua_nilai']; ?></td>
                    <td><?php echo round($row['nnr_per_responden_kali_0_11'], 2); ?></td> <!-- Tampilkan NNR -->
                    <td><?php echo round($row['nilai_rata_rata_kali_25'], 2); ?></td> <!-- Tampilkan nilai rata-rata x 25 -->
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="14">Tidak ada data ditemukan untuk tahun ini.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
