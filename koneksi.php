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

// Default jumlah data per halaman
$default_limit = 10;
$limit = isset($_GET['rows_per_page']) ? (int)$_GET['rows_per_page'] : $default_limit;

// Validasi limit
$allowed_limits = [10, 25, 50, 100];
if (!in_array($limit, $allowed_limits)) {
    $limit = $default_limit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Hitung offset berdasarkan halaman
$offset = ($page - 1) * $limit;

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

// Query untuk menghitung total data
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

// Eksekusi query untuk menghitung total data
$total_result = $conn->query($total_query);
if ($total_result) {
    $total_row = $total_result->fetch_assoc();
    $total_data = $total_row['total'];
} else {
    $total_data = 0; // Jika query gagal, total data dianggap 0
}

// Hitung total halaman
$total_pages = ceil($total_data / $limit);

// Tambahkan limit dan offset pada query utama
$sql .= " LIMIT $limit OFFSET $offset";

// Eksekusi query utama
$result = $conn->query($sql);

// Pastikan variabel-variabel yang digunakan di pagination sudah terdefinisi
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filter_triwulan = isset($_GET['triwulan']) ? $_GET['triwulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$rows_per_page = isset($_GET['rows_per_page']) ? (int)$_GET['rows_per_page'] : 10;
$total_pages = ceil($total_data / $rows_per_page); // Hitung total halaman sesuai rows per page yang dipilih



// Logika untuk unduh CSV
if (isset($_GET['download'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="data_ikm.csv"');
    echo "\xEF\xBB\xBF"; // Untuk encoding UTF-8
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Umur', 'Jenis Kelamin', 'Pekerjaan', 'Pendidikan', 'U1', 'U2', 'U3', 'U4', 'U5', 'U6', 'U7', 'U8', 'U9', 'Waktu'], ";");

    // Hapus LIMIT dan OFFSET untuk mengambil semua data yang sesuai dengan filter
    $csv_query = "SELECT * FROM index_kepuasan_masyarakat WHERE 1=1";

    // Filter Triwulan
    if (!empty($filter_triwulan)) {
        switch ($filter_triwulan) {
            case 1:
                $csv_query .= " AND MONTH(waktu) BETWEEN 1 AND 3";
                break;
            case 2:
                $csv_query .= " AND MONTH(waktu) BETWEEN 4 AND 6";
                break;
            case 3:
                $csv_query .= " AND MONTH(waktu) BETWEEN 7 AND 9";
                break;
            case 4:
                $csv_query .= " AND MONTH(waktu) BETWEEN 10 AND 12";
                break;
        }
    }

    // Filter Tahun
    if (!empty($filter_tahun)) {
        $csv_query .= " AND YEAR(waktu) = $filter_tahun";
    }

    // Eksekusi query untuk CSV
    $csv_result = $conn->query($csv_query);
    while ($row = $csv_result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'], $row['umur'], $row['jenis_kelamin'], $row['pekerjaan'], $row['pendidikan'],
            $row['nilai_1'], $row['nilai_2'], $row['nilai_3'], $row['nilai_4'], $row['nilai_5'],
            $row['nilai_6'], $row['nilai_7'], $row['nilai_8'], $row['nilai_9'], formatTanggal($row['waktu'])
        ], ";");
    }
    fclose($output);
    exit();
}


// Logika untuk unduh PDF
if (isset($_GET['download_pdf'])) {
    $filter_tahun = $_GET['tahun'] ?? 'Semua Tahun';
    $filter_triwulan = $_GET['triwulan'] ?? 'Semua Triwulan';

    // Validasi dan konversi filter
    $filter_tahun = ($filter_tahun !== 'Semua Tahun' && is_numeric($filter_tahun)) ? intval($filter_tahun) : 'Semua Tahun';
    $filter_triwulan = ($filter_triwulan !== 'Semua Triwulan' && in_array($filter_triwulan, [1, 2, 3, 4])) ? intval($filter_triwulan) : 'Semua Triwulan';

    // Membuat instance TCPDF
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Bappeda Grobogan');
    $pdf->SetTitle('Data Suevey Kepuasan Masyarakat');
    $pdf->SetMargins(15, 20, 15);
    $pdf->AddPage();

    // Judul utama
    $pdf->SetFont('helvetica', 'B', 22);
    $pdf->Cell(0, 10, 'DATA SURVEY KEPUASAN MASYARAKAT', 0, 1, 'C');

    // Subjudul berdasarkan filter
    $subjudul = "Tahun: $filter_tahun | Triwulan: $filter_triwulan";
    $pdf->SetFont('helvetica', '', 14);
    $pdf->Cell(0, 10, $subjudul, 0, 1, 'C');

    // Header tabel
    $col_widths = [12, 15, 25, 40, 33, 10, 10, 10, 10, 10, 10, 10, 10, 10, 57];
    $col_headers = ['ID', 'Umur', 'Jenis Kelamin', 'Pekerjaan', 'Pendidikan', 'U1', 'U2', 'U3', 'U4', 'U5', 'U6', 'U7', 'U8', 'U9', 'Waktu'];

    $html = '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; text-align:center;">';
    $html .= '<thead><tr style="background-color:#1c81e6; color:#ffffff;">';
    foreach ($col_headers as $header) {
        $html .= '<th style="width:' . $col_widths[array_search($header, $col_headers)] . 'mm; text-align:center; vertical-align:middle;">' . $header . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    // Query untuk filter PDF
    $pdf_query = "SELECT * FROM index_kepuasan_masyarakat WHERE 1=1";

    if ($filter_triwulan !== 'Semua Triwulan') {
        $bulan_awal = ($filter_triwulan - 1) * 3 + 1;
        $bulan_akhir = $bulan_awal + 2;
        $pdf_query .= " AND MONTH(waktu) BETWEEN $bulan_awal AND $bulan_akhir";
    }

    if ($filter_tahun !== 'Semua Tahun') {
        $pdf_query .= " AND YEAR(waktu) = $filter_tahun";
    }

    // Eksekusi query
    $pdf_result = $conn->query($pdf_query);

    if ($pdf_result->num_rows > 0) {
        while ($row = $pdf_result->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td style="width:' . $col_widths[0] . 'mm;">' . $row['id'] . '</td>';
            $html .= '<td style="width:' . $col_widths[1] . 'mm;">' . $row['umur'] . '</td>';
            $html .= '<td style="width:' . $col_widths[2] . 'mm;">' . $row['jenis_kelamin'] . '</td>';
            $html .= '<td style="width:' . $col_widths[3] . 'mm;">' . $row['pekerjaan'] . '</td>';
            $html .= '<td style="width:' . $col_widths[4] . 'mm;">' . $row['pendidikan'] . '</td>';
            for ($i = 5; $i <= 13; $i++) {
                $nilai = 'nilai_' . ($i - 4);
                $html .= '<td style="width:' . $col_widths[$i] . 'mm;">' . $row[$nilai] . '</td>';
            }
            $html .= '<td style="width:' . $col_widths[14] . 'mm;">' . formatTanggal($row['waktu']) . '</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="15" style="text-align:center;">Data tidak ditemukan</td></tr>';
    }

    $html .= '</tbody></table>';

    // Menulis tabel ke PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Output PDF
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
    <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="progress.php" target="_blank">
            <i class="fas fa-chart-line"></i>Laporan IKM
        </a>
    </li>
</ul>

    </div>

    <div class="content p-4">

    <!-- Fungsi Logout dengan Pop-Up -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Tombol Logout -->
                <form id="logoutForm" action="logout.php" method="post" class="d-flex">
                    <button type="button" class="btn btn-danger" onclick="confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </ul>
        </div>
    </div>
</nav>

        <div class="laporan">
            <h1>DATA SURVEY KEPUASAN MASYARAKAT</h1>
        </div>

<!-- Form Filter dan Tombol Download -->
<form method="get" action="" class="d-flex justify-content-between align-items-center mb-3">
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

    <!-- Tombol Download dan Dropdown Jumlah Data -->
    <div class="d-flex align-items-center gap-2">
        <!-- Dropdown Jumlah Data -->
        <select name="rows_per_page" class="form-select" style="width: auto;">
            <option value="10" <?php if (isset($_GET['rows_per_page']) && $_GET['rows_per_page'] == '10') echo 'selected'; ?>>10</option>
            <option value="25" <?php if (isset($_GET['rows_per_page']) && $_GET['rows_per_page'] == '25') echo 'selected'; ?>>25</option>
            <option value="50" <?php if (isset($_GET['rows_per_page']) && $_GET['rows_per_page'] == '50') echo 'selected'; ?>>50</option>
            <option value="100" <?php if (isset($_GET['rows_per_page']) && $_GET['rows_per_page'] == '100') echo 'selected'; ?>>100</option>
        </select>

        <!-- Tombol Unduh Data -->
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
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&triwulan=<?php echo $filter_triwulan; ?>&tahun=<?php echo $filter_tahun; ?>&rows_per_page=<?php echo $rows_per_page; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <?php
        $range = 2; 
        $start = max(1, $page - $range);
        $end = min($total_pages, $page + $range);

        if ($start > 1) {
            echo '<li class="page-item"><a class="page-link" href="?page=1&triwulan=' . $filter_triwulan . '&tahun=' . $filter_tahun . '&rows_per_page=' . $rows_per_page . '">1</a></li>';
            if ($start > 2) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        for ($i = $start; $i <= $end; $i++):
            ?>
            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&triwulan=<?php echo $filter_triwulan; ?>&tahun=<?php echo $filter_tahun; ?>&rows_per_page=<?php echo $rows_per_page; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; 

        if ($end < $total_pages) {
            if ($end < $total_pages - 1) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&triwulan=' . $filter_triwulan . '&tahun=' . $filter_tahun . '&rows_per_page=' . $rows_per_page . '">' . $total_pages . '</a></li>';
        }
        ?>
        
        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&triwulan=<?php echo $filter_triwulan; ?>&tahun=<?php echo $filter_tahun; ?>&rows_per_page=<?php echo $rows_per_page; ?>" aria-label="Next">
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
<script>
    function confirmLogout() {
        const userConfirmed = confirm("Apakah Anda yakin ingin logout?");
        if (userConfirmed) {
            // Submit form untuk logout di server
            document.getElementById('logoutForm').submit();

            // Redirect ke login.php setelah logout
            setTimeout(() => {
                window.location.href = "login.php";
            }, 500); // Delay agar form sempat terkirim
        }
    }
</script>
</body>
</html>


<?php
$conn->close();
?>
