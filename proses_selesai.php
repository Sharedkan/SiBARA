<?php
// proses_selesai.php
session_start();
header('Content-Type: application/json');

$host = 'localhost';
$db   = 'db_pengadaan_aset';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ambil data dari payload aman / POST
        $permintaan_id = filter_input(INPUT_POST, 'permintaan_id', FILTER_SANITIZE_NUMBER_INT);
        $sumber_dana   = filter_input(INPUT_POST, 'sumber_dana', FILTER_SANITIZE_STRING);

        // Mulai Transaksi Database (ACID Compliant)
        $pdo->beginTransaction();

        // 1. Ambil informasi pengajuan dan pemohon
        $stmtReq = $pdo->prepare("SELECT user_id, vendor_id FROM permintaan_barang WHERE id = ?");
        $stmtReq->execute([$permintaan_id]);
        $reqData = $stmtReq->fetch(PDO::FETCH_ASSOC);

        if (!$reqData) {
            throw new Exception("Data permintaan tidak ditemukan.");
        }

        $bidang_id = $reqData['user_id'];
        $vendor_id = $reqData['vendor_id'];

        // 2. Ambil detail barang yang disetujui vendor
        $stmtDetail = $pdo->prepare("SELECT * FROM detail_permintaan WHERE permintaan_id = ?");
        $stmtDetail->execute([$permintaan_id]);
        $items = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

        // 3. Looping item untuk sinkronisasi otomatis ke inventaris aset tetap
        $stmtInventaris = $pdo->prepare("INSERT INTO inventaris (kode_inventaris, nama_barang, spesifikasi, bidang_pemegang_id, tanggal_pembelian, sumber_dana, status_kelayakan, vendor_pengadaan_id) VALUES (?, ?, ?, ?, CURDATE(), ?, 'Baik', ?)");

        foreach ($items as $item) {
            if ($item['sifat_barang'] === 'Tidak_Habis_Pakai' && $item['jumlah_vendor'] > 0) {
                // Buat item inventaris sejumlah kuantitas riil yang dikirim vendor
                for ($i = 1; $i <= $item['jumlah_vendor']; $i++) {
                    $kode_unik = "INV/" . date('Y') . "/" . strtoupper(substr(md5(uniqid()), 0, 6));
                    $spesifikasi = $item['spesifikasi'] ?? 'Pengadaan baru via vendor';

                    $stmtInventaris->execute([
                        $kode_unik,
                        $item['nama_barang'],
                        $spesifikasi,
                        $bidang_id,
                        $sumber_dana,
                        $vendor_id
                    ]);
                }
            }
        }

        // 4. Perbarui status master permintaan menjadi Completed
        $stmtUpdate = $pdo->prepare("UPDATE permintaan_barang SET status = 'Completed', sumber_dana = ? WHERE id = ?");
        $stmtUpdate->execute([$sumber_dana, $permintaan_id]);

        // Commit transaksi jika semua berjalan lancar tanpa error
        $pdo->commit();

        echo json_encode([
            "status" => "success", 
            "message" => "Pengadaan berhasil diselesaikan. Aset tidak habis pakai telah otomatis terdaftar di sistem inventaris."
        ]);
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>