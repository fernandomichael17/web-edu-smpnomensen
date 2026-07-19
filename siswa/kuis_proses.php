<?php
/**
 * File: siswa/kuis_proses.php
 * Deskripsi: Memproses jawaban kuis siswa, menghitung skor secara aman di sisi server,
 *            menyimpan hasil ke tb_hasil, dan mengarahkan ke halaman hasil kuis.
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

// Memanggil konfigurasi database
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kuis.php");
    exit();
}

$id_kuis = isset($_POST['id_kuis']) ? intval($_POST['id_kuis']) : 0;
$id_siswa = $_SESSION['siswa_id'];
$elapsed_time_sec = isset($_POST['elapsed_time']) ? intval($_POST['elapsed_time']) : 0;

// Ambil jawaban dari POST
$student_answers = isset($_POST['answers']) ? json_decode($_POST['answers'], true) : [];

try {
    // Ambil semua soal untuk kuis ini dari DB untuk mencocokkan jawaban
    $stmt = $pdo->prepare("SELECT id_soal, jawaban_benar FROM tb_soal WHERE id_kuis = :id_kuis ORDER BY id_soal ASC");
    $stmt->execute(['id_kuis' => $id_kuis]);
    $soal_list = $stmt->fetchAll();
    
    $total_soal = count($soal_list);
    if ($total_soal === 0) {
        header("Location: kuis.php");
        exit();
    }

    $jumlah_benar = 0;
    $jumlah_salah = 0;

    // Hitung jawaban benar & salah
    foreach ($soal_list as $index => $soal) {
        $selected_opt = isset($student_answers[$index]) ? trim($student_answers[$index]) : '';
        $correct_opt = trim($soal['jawaban_benar']);

        if ($selected_opt === $correct_opt) {
            $jumlah_benar++;
        } else {
            $jumlah_salah++;
        }
    }

    // Hitung Skor (Skala 100)
    $skor = round(($jumlah_benar / $total_soal) * 100);

    // Simpan ke tb_hasil
    $stmt_insert = $pdo->prepare("INSERT INTO tb_hasil (id_siswa, id_kuis, skor, jumlah_benar, jumlah_salah) VALUES (:id_siswa, :id_kuis, :skor, :jumlah_benar, :jumlah_salah)");
    $stmt_insert->execute([
        'id_siswa' => $id_siswa,
        'id_kuis' => $id_kuis,
        'skor' => $skor,
        'jumlah_benar' => $jumlah_benar,
        'jumlah_salah' => $jumlah_salah
    ]);
    
    $id_hasil = $pdo->lastInsertId();

    // Simpan hasil ke session untuk ditampilkan di hasil_kuis.php
    $_SESSION['last_kuis_result'] = [
        'id_hasil' => $id_hasil,
        'id_kuis' => $id_kuis,
        'skor' => $skor,
        'jumlah_benar' => $jumlah_benar,
        'jumlah_salah' => $jumlah_salah,
        'elapsed_time' => $elapsed_time_sec
    ];

    header("Location: hasil_kuis.php");
    exit();

} catch (PDOException $e) {
    die("Gagal memproses hasil kuis: " . $e->getMessage());
}
