<?php
// Memasukkan library TCPDF
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

// Inisialisasi variabel filter
$filter_triwulan = isset($_GET['triwulan']) ? (int)$_GET['triwulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : '';


// Pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini
$offset = ($page - 1) * $limit; // Hitung offset berdasarkan halaman

// Query dasar
$sql = "SELECT * FROM index_kepuasan_masyarakat WHERE 1=1";

// Filter Triwulan
if (!empty($filter_triwulan)) {
    switch ($filter_triwulan) {
        case 1:
            $sql .= " AND MONTH(waktu) BETWEEN 1 AND 3";
            break;
        case 2:
            $sql .= " AND MONTH(waktu) BETWEEN 4 AND 6";
            break;
        case 3:
            $sql .= " AND MONTH(waktu) BETWEEN 7 AND 9";
            break;
        case 4:
            $sql .= " AND MONTH(waktu) BETWEEN 10 AND 12";
            break;
    }
}

// Filter Tahun
if (!empty($filter_tahun)) {
    $sql .= " AND YEAR(waktu) = $filter_tahun";
}

// Query untuk mengambil total data yang sesuai filter
$total_query = "SELECT COUNT(*) as total FROM index_kepuasan_masyarakat WHERE 1=1";

// Tambahkan kondisi filter pada query total data
if (!empty($filter_triwulan)) {
    switch ($filter_triwulan) {
        case 1:
            $total_query .= " AND MONTH(waktu) BETWEEN 1 AND 3";
            break;
        case 2:
            $total_query .= " AND MONTH(waktu) BETWEEN 4 AND 6";
            break;
        case 3:
            $total_query .= " AND MONTH(waktu) BETWEEN 7 AND 9";
            break;
        case 4:
            $total_query .= " AND MONTH(waktu) BETWEEN 10 AND 12";
            break;
    }
}
if (!empty($filter_tahun)) {
    $total_query .= " AND YEAR(waktu) = $filter_tahun";
}

// Hitung total data
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_data = $total_row['total'];

// Hitung total halaman
$total_pages = ceil($total_data / $limit);

// Tambahkan limit dan offset pada query utama
$sql .= " LIMIT $offset, $limit";

// Eksekusi query utama
$result = $conn->query($sql);


// Logika untuk unduh CSV
if (isset($_GET['download'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="data_ikm.csv"');

    echo "\xEF\xBB\xBF"; // Untuk encoding UTF-8
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Umur', 'Jenis Kelamin', 'Pekerjaan', 'Pendidikan', 'U1', 'U2', 'U3', 'U4', 'U5', 'U6', 'U7', 'U8', 'U9', 'Waktu'], ";");

    // Ambil filter triwulan dan tahun
    $filter_triwulan = isset($_GET['triwulan']) ? $_GET['triwulan'] : '';
    $filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';

    // Query dasar
    $query = "SELECT * FROM index_kepuasan_masyarakat WHERE 1=1";

    // Tambahkan filter Triwulan
    if (!empty($filter_triwulan)) {
        switch ($filter_triwulan) {
            case 1:
                $query .= " AND MONTH(waktu) BETWEEN 1 AND 3";
                break;
            case 2:
                $query .= " AND MONTH(waktu) BETWEEN 4 AND 6";
                break;
            case 3:
                $query .= " AND MONTH(waktu) BETWEEN 7 AND 9";
                break;
            case 4:
                $query .= " AND MONTH(waktu) BETWEEN 10 AND 12";
                break;
        }
    }

    // Tambahkan filter Tahun
    if (!empty($filter_tahun)) {
        $query .= " AND YEAR(waktu) = $filter_tahun";
    }

    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'], $row['umur'], $row['jenis_kelamin'], $row['pekerjaan'], $row['pendidikan'],
            $row['nilai_1'], $row['nilai_2'], $row['nilai_3'], $row['nilai_4'], $row['nilai_5'],
            $row['nilai_6'], $row['nilai_7'], $row['nilai_8'], $row['nilai_9'], formatTanggal($row['waktu'])
        ], ";");
    }
    fclose($output);
    exit();
}


// Proses download PDF
if (isset($_GET['download_pdf'])) {
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Data Kepuasan Masyarakat', 0, 1, 'C');

    // Table header
    $html = '<table border="1" cellpadding="2" cellspacing="0" style="width:100%; text-align:center;">';
    $html .= '<thead><tr style="background-color:#1c81e6; color:#ffffff;">';
    $html .= '<th>ID</th><th>Umur</th><th>Jenis Kelamin</th><th>Pekerjaan</th><th>Pendidikan</th>';
    $html .= '<th>U1</th><th>U2</th><th>U3</th><th>U4</th><th>U5</th><th>U6</th><th>U7</th><th>U8</th><th>U9</th><th>Waktu</th></tr></thead><tbody>';

    // Ambil filter triwulan dan tahun
    $filter_triwulan = isset($_GET['triwulan']) ? $_GET['triwulan'] : '';
    $filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';

    // Query dasar
    $query = "SELECT * FROM index_kepuasan_masyarakat WHERE 1=1";

    // Filter Triwulan
    if (!empty($filter_triwulan)) {
        switch ($filter_triwulan) {
            case 1:
                $query .= " AND MONTH(waktu) BETWEEN 1 AND 3";  // Triwulan 1
                break;
            case 2:
                $query .= " AND MONTH(waktu) BETWEEN 4 AND 6";  // Triwulan 2
                break;
            case 3:
                $query .= " AND MONTH(waktu) BETWEEN 7 AND 9";  // Triwulan 3
                break;
            case 4:
                $query .= " AND MONTH(waktu) BETWEEN 10 AND 12"; // Triwulan 4
                break;
        }
    }

    // Filter Tahun
    if (!empty($filter_tahun)) {
        $query .= " AND YEAR(waktu) = $filter_tahun";
    }

    // Menjalankan query
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $row['id'] . '</td>';
        $html .= '<td>' . $row['umur'] . '</td>';
        $html .= '<td>' . $row['jenis_kelamin'] . '</td>';
        $html .= '<td>' . $row['pekerjaan'] . '</td>';
        $html .= '<td>' . $row['pendidikan'] . '</td>';
        $html .= '<td>' . $row['nilai_1'] . '</td>';
        $html .= '<td>' . $row['nilai_2'] . '</td>';
        $html .= '<td>' . $row['nilai_3'] . '</td>';
        $html .= '<td>' . $row['nilai_4'] . '</td>';
        $html .= '<td>' . $row['nilai_5'] . '</td>';
        $html .= '<td>' . $row['nilai_6'] . '</td>';
        $html .= '<td>' . $row['nilai_7'] . '</td>';
        $html .= '<td>' . $row['nilai_8'] . '</td>';
        $html .= '<td>' . $row['nilai_9'] . '</td>';
        $html .= '<td>' . formatTanggal($row['waktu']) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    // Menulis HTML ke dalam PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Menyimpan atau menampilkan PDF
    $pdf->Output('data_kepuasan_masyarakat.pdf', 'I');
    exit();

}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kepuasan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Sidebar -->
<div class="d-flex">
    <div class="sidebar bg-light p-3">
        <h4>ADMIN SIIKUAT</h4>
        <ul class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link active" aria-current="page" href="rar.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="koneksi.php">
            <i class="fas fa-file-alt"></i>IKM
        </a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user"></i> User
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#"><i class="fas fa-user-plus"></i> Buat Akun</a></li>
            <li><a class="dropdown-item" href="login.php"><i class="fas fa-sign-out-alt"></i> Log out</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#"><i class="fas fa-key"></i> Ganti Password</a></li>
        </ul>
    </li>
    <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="progress.php">
            <i class="fas fa-chart-line"></i>Laporan IKM
        </a>
    </li>
</ul>

    </div>

    <div class="content p-4">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="path/to/profile.jpg" alt="Profile Picture" class="profile-pic"> <!-- Ganti dengan path gambar profil -->
                            Hello, Admin <!-- Ganti dengan nama pengguna yang login -->
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
        <div class="laporan">
            <h1>DATA SURVEY KEPUASAN MASYARAKAT</h1>
        </div>

        <!-- Tombol untuk download data -->
<!-- <form method="get" action="">
    <button type="submit" name="download" class="btn btn-primary mb-3">
        <i class="fas fa-download"></i> Unduh Data
    </button>
    <button type="submit" name="download_pdf" class="btn btn-secondary mb-3">
        <i class="fas fa-file-pdf"></i> Download PDF
    </button>
</form> -->

<!-- Form Filter dan Tombol Download -->
<form method="get" action="" class="d-flex justify-content-between align-items-center mb-3">
    <!-- Filter Triwulan dan Tahun -->
    <div class="d-flex gap-2">
        <!-- Filter Triwulan -->
        <select name="triwulan" class="form-select" style="width: auto;">
            <option value="" <?php if (!isset($_GET['triwulan']) || $_GET['triwulan'] === '') echo 'selected'; ?>>Semua Triwulan</option>
            <option value="1" <?php if (isset($_GET['triwulan']) && $_GET['triwulan'] == '1') echo 'selected'; ?>>Triwulan 1</option>
            <option value="2" <?php if (isset($_GET['triwulan']) && $_GET['triwulan'] == '2') echo 'selected'; ?>>Triwulan 2</option>
            <option value="3" <?php if (isset($_GET['triwulan']) && $_GET['triwulan'] == '3') echo 'selected'; ?>>Triwulan 3</option>
            <option value="4" <?php if (isset($_GET['triwulan']) && $_GET['triwulan'] == '4') echo 'selected'; ?>>Triwulan 4</option>
        </select>

        <!-- Filter Tahun -->
        <select name="tahun" class="form-select" style="width: auto;">
            <option value="" <?php if (!isset($_GET['tahun']) || $_GET['tahun'] === '') echo 'selected'; ?>>Semua Tahun</option>
            <?php
            $current_year = date("Y");
            for ($year = 2023; $year <= $current_year; $year++): ?>
                <option value="<?php echo $year; ?>" <?php if (isset($_GET['tahun']) && $_GET['tahun'] == $year) echo 'selected'; ?>>
                    <?php echo $year; ?>
                </option>
            <?php endfor; ?>
        </select>

        <!-- Tombol Filter -->
        <button type="submit" class="btn btn-info">
            <i class="fas fa-filter"></i> Terapkan
        </button>
    </div>

    <!-- Tombol Download -->
    <div>
        <button type="submit" name="download" class="btn btn-primary">
            <i class="fas fa-download"></i> Unduh Data
        </button>
        <button type="submit" name="download_pdf" class="btn btn-secondary">
            <i class="fas fa-file-pdf"></i> Download PDF
        </button>
    </div>
</form>



        <br>

        
        <!-- Tabel Data -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Umur</th>
                    <th>Jenis Kelamin</th>
                    <th>Pekerjaan</th>
                    <th>Pendidikan</th>
                    <th>U1</th>
                    <th>U2</th>
                    <th>U3</th>
                    <th>U4</th>
                    <th>U5</th>
                    <th>U6</th>
                    <th>U7</th>
                    <th>U8</th>
                    <th>U9</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['umur']; ?></td>
                            <td><?php echo $row['jenis_kelamin']; ?></td>
                            <td><?php echo $row['pekerjaan']; ?></td>
                            <td><?php echo $row['pendidikan']; ?></td>
                            <td><?php echo $row['nilai_1']; ?></td>
                            <td><?php echo $row['nilai_2']; ?></td>
                            <td><?php echo $row['nilai_3']; ?></td>
                            <td><?php echo $row['nilai_4']; ?></td>
                            <td><?php echo $row['nilai_5']; ?></td>
                            <td><?php echo $row['nilai_6']; ?></td>
                            <td><?php echo $row['nilai_7']; ?></td>
                            <td><?php echo $row['nilai_8']; ?></td>
                            <td><?php echo $row['nilai_9']; ?></td>
                            <td><?php echo formatTanggal($row['waktu']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="16">Tidak ada data ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mb-4">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <?php
                $range = 2; 
                $start = max(1, $page - $range);
                $end = min($total_pages, $page + $range);
                
                if ($start > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                    if ($start > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; 

                if ($end < $total_pages) {
                    if ($end < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                }
                ?>
                
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>

    </div>
</div>

<!-- Footer -->
<footer class="footer mt-auto">
    <div class="container">
        <span class="text-muted">© 2024 Data Kepuasan Masyarakat. All Rights Reserved.</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

</body>
</html>


<?php
$conn->close();
?>
