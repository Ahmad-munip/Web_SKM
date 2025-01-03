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

// Fungsi untuk memformat tanggal dalam Bahasa Indonesia
function formatTanggal($date) {
    $hariArray = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulanArray = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $dayOfWeek = date('w', strtotime($date));
    $day = date('d', strtotime($date));
    $month = date('n', strtotime($date)) - 1;
    $year = date('Y', strtotime($date));

    return $hariArray[$dayOfWeek] . ", " . $day . " " . $bulanArray[$month] . " " . $year;
}

// Proses input data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $umur = $_POST['umur'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $pekerjaan = $_POST['pekerjaan'];
    $pendidikan = $_POST['pendidikan'];

    // Input nilai 1 sampai 9
    $nilai_1 = isset($_POST['nilai_1']) ? (int)$_POST['nilai_1'] : 0;
    $nilai_2 = isset($_POST['nilai_2']) ? (int)$_POST['nilai_2'] : 0;
    $nilai_3 = isset($_POST['nilai_3']) ? (int)$_POST['nilai_3'] : 0;
    $nilai_4 = isset($_POST['nilai_4']) ? (int)$_POST['nilai_4'] : 0;
    $nilai_5 = isset($_POST['nilai_5']) ? (int)$_POST['nilai_5'] : 0;
    $nilai_6 = isset($_POST['nilai_6']) ? (int)$_POST['nilai_6'] : 0;
    $nilai_7 = isset($_POST['nilai_7']) ? (int)$_POST['nilai_7'] : 0;
    $nilai_8 = isset($_POST['nilai_8']) ? (int)$_POST['nilai_8'] : 0;
    $nilai_9 = isset($_POST['nilai_9']) ? (int)$_POST['nilai_9'] : 0;

    // Validasi untuk memastikan pendidikan tidak kosong
    if (empty($pendidikan)) {
        echo "<script>alert('Pendidikan tidak boleh kosong.'); window.history.back();</script>";
    } else {
        // Siapkan statement untuk memasukkan data
        $stmt = $conn->prepare("INSERT INTO index_kepuasan_masyarakat (umur, jenis_kelamin, pekerjaan, pendidikan, nilai_1, nilai_2, nilai_3, nilai_4, nilai_5, nilai_6, nilai_7, nilai_8, nilai_9, waktu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssiiiiiiiii", $umur, $jenis_kelamin, $pekerjaan, $pendidikan, $nilai_1, $nilai_2, $nilai_3, $nilai_4, $nilai_5, $nilai_6, $nilai_7, $nilai_8, $nilai_9);
        
        if ($stmt->execute()) {
            echo "<script>alert('✨ Data berhasil ditambahkan! Terima kasih sudah berpartisipasi! 🎉'); window.location.href = 'form.php';</script>";
        } else {
            echo "<script>alert('😞 Oops! Terjadi kesalahan saat menambahkan data: " . $stmt->error . "'); window.history.back();</script>";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Kepuasan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/kuis.css">
</head>
<body>
<div class="container mt-5">
    <h2>Survey Kepuasan Masyarakat</h2>
    <form method="post" action="" id="mainForm" onsubmit="return validateForm()">
    <div class="mb-3">
    <label for="umur" class="form-label">
        <i class="fas fa-user"></i> Umur
    </label>
    <input type="number" class="form-control" name="umur" required>
</div>
<div class="mb-3">
    <label for="jenis_kelamin" class="form-label">
        <i class="fas fa-venus-mars"></i> Jenis Kelamin
    </label>
    <select name="jenis_kelamin" class="form-control" required>
        <option value="L">Laki-Laki</option>
        <option value="P">Perempuan</option>
    </select>
</div>
<div class="mb-3">
    <label for="pekerjaan" class="form-label">
        <i class="fas fa-briefcase"></i> Pekerjaan Utama
    </label>
    <select name="pekerjaan" class="form-control" required>
        <option value="Tidak Bekerja">Tidak Bekerja</option>
        <option value="ASN/TNI/POLRI"> ASN/TNI/POLRI </option>
        <option value="Wiraswasta/Usahawan"> Wiraswasta/Usahawan </option>
        <option value="Pelajar/Mahasiswa">Pelajar/Mahasiswa</option>
        <option value="Lainnya">Lainnya</option>
    </select>
</div>
<div class="mb-3">
    <label for="pendidikan" class="form-label">
        <i class="fas fa-graduation-cap"></i> Pendidikan Terakhir
    </label>
    <select name="pendidikan" class="form-control" required>
        <option value="SD">SD</option>
        <option value="SMP">SMP</option>
        <option value="SMA">SMA</option>
        <option value="D1, D2, dan D3">D1, D2, dan D3</option>
        <option value="S1">S1</option>
        <option value="S2 ke atas">S2 ke atas</option>
    </select>
</div>

        <button type="button" class="btn btn-primary mt-3" onclick="showQuestionModal()">Lanjutkan ke Pertanyaan</button>
    </form>
</div>

<!-- Modal Pop-up Pertanyaan -->
<div class="modal fade" id="questionModal" tabindex="-1" aria-labelledby="questionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="questionModalLabel">Pertanyaan <span id="questionNumber"></span> dari 9</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 id="questionTitle" class="text-center mb-3"></h4>
                <p id="questionText" class="text-center" style="font-size: 1.1rem;"></p>
                <div class="nilai-wrapper" id="nilaiOptions"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="backButton">Back</button>
                <button type="button" class="btn btn-primary" onclick="nextQuestion()">Next</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="currentQuestion" name="currentQuestion" value="0">
<input type="submit" class="btn btn-success mt-3" value="Kirim Data" style="display:none;" id="submitBtn">
</form>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const questions = [
    { title: "Kesesuaian Persyaratan Pelayanan dgn Jenis Pelayanan", text: "Bagaimana pendapat Bapak/Ibu/Sdr/i tentang kesesuaian persyaratan pelayanan dengan jenis pelayanan di instansi kami?" },
    { title: "Pemahaman terhadap kemudahan prosedur pelayanan", text: "Bagaimana pemahaman Bapak/Ibu/Sdr/i tentang kemudahan prosedur pelayanan di instansi kami?" },
    { title: "Kecepatan waktu dalam memberikan pelayanan", text: "Bagaimana pendapat Bapak/Ibu/Sdr/i tentang kecepatan waktu dalam memberikan pelayanan di instansi kami?" },
    { title: "Kewajaran Biaya/tarif", text: "Bagaimana pendapat Bapak/Ibu/Sdr/i tentang kewajaran Biaya/tarif dalam memperoleh pelayanan?" },
    { title: "Kesesuaian produk layanan", text: "Bagaimana pendapat Bapak/Ibu/Sdr/i tentang Kesesuaian produk layanan antara yang tercantum dalam standar pelayanan dengan hasil yang diberikan" },
    { title: "Kompetensi kemampuan petugas", text: "Bagaimana pendapat Bapak/Ibu/Sdr/i tentang kompetensi/kemampuan petugas dalam pelayanan di instansi kami?" },
    { title: "Perilaku petugas dalam pelayanan", text: "Bagaimana pendapat Bapak/Ibu/Sdr/i tentang perilaku petugas kami dalam pelayanan terkait kesopanan dan keramahan?" },
    { title: "Kualitas Sarana dan Prasarana", text: "Bagaimana pendapat Bapak/Ibu/Sdr/i tentang kualitas sarana dan prasarana di instansi kami?" },
    { title: "Penanganan Pengaduan Pengguna Layanan", text: "Bagaimana pendapat Bapak/Ibu/Sdr/i tentang penanganan pengaduan pengguna layanan di instansi kami?" }
];

const labels = ["😡 Sangat Buruk", "😕 Buruk", "😊 Baik", "😍 Sangat Baik"];
let currentQuestion = 0;

function showQuestionModal() {
  // Ambil elemen input umur
  const umurInput = document.querySelector("input[name='umur']");
    const umur = umurInput.value.trim();

    // Validasi umur
    if (!umur) {
        alert("Harap isi bagian umur.");
        umurInput.focus(); // Fokus pada input umur
        return;
    }
    if (isNaN(umur) || parseInt(umur) <= 0) {
        alert("Masukkan umur yang valid (angka positif).");
        umurInput.focus(); // Fokus pada input umur
        return;
    }

    if (currentQuestion < questions.length) {
        document.getElementById("questionNumber").innerText = currentQuestion + 1;
        document.getElementById("questionTitle").innerHTML = `<i class="fas fa-question-circle"></i> ${questions[currentQuestion].title}`;
        document.getElementById("questionText").innerText = questions[currentQuestion].text;
        renderOptions();
        const modal = new bootstrap.Modal(document.getElementById('questionModal'), { backdrop: 'static' });
        modal.show();
    }
}

function renderOptions() {
    const nilaiOptions = document.getElementById("nilaiOptions");
    nilaiOptions.innerHTML = '';
    for (let i = 1; i <= 4; i++) {
        const isChecked = i === 4 ? "checked" : ""; // Default ke "Sangat Baik"
        nilaiOptions.innerHTML += `
            <div class="nilai-input">
                <input type="radio" id="nilai${i}" name="nilai" value="${i}" ${isChecked} required>
                <label for="nilai${i}">${labels[i - 1]}</label>
            </div>
        `;
    }
}

function nextQuestion() {
    const selectedValue = document.querySelector(`input[name="nilai"]:checked`);
    if (!selectedValue) {
        alert("Harap pilih salah satu opsi.");
        return;
    }

    const form = document.getElementById('mainForm');
    let inputHidden = document.getElementById(`nilai_${currentQuestion + 1}`);
    if (!inputHidden) {
        inputHidden = document.createElement('input');
        inputHidden.type = 'hidden';
        inputHidden.name = `nilai_${currentQuestion + 1}`;
        inputHidden.id = `nilai_${currentQuestion + 1}`;
        form.appendChild(inputHidden);
    }
    inputHidden.value = selectedValue.value;

    // Reset checked state for next question
    selectedValue.checked = false;

    currentQuestion++;
    if (currentQuestion < questions.length) {
        showQuestionModal();
    } else {
        form.submit(); // Otomatis mengirim form setelah semua pertanyaan dijawab
    }
}


document.getElementById("backButton").addEventListener("click", function () {
    if (currentQuestion > 0) {
        currentQuestion--;
        showQuestionModal();
    }
});

function validateForm() {
    for (let i = 1; i <= questions.length; i++) {
        const selectedValue = document.getElementById(`nilai_${i}`);
        if (!selectedValue || !selectedValue.value) {
            alert(`Harap jawab semua pertanyaan (${i}/${questions.length} belum dijawab).`);
            return false;
        }
    }
    return true; // Izinkan pengiriman form
}

// Tambahkan event listener untuk input umur
document.querySelector("input[name='umur']").addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
        e.preventDefault(); // Hindari form dikirim
        showQuestionModal(); // Tampilkan modal
    }
});

</script>


</body>
</html>
