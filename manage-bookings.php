<?php
require_once 'includes/functions.php';
require_once 'includes/admin-functions.php';

require_admin();

$db = getConnection();
$message = "";

// Validasi & proses update status booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_booking'])) {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

    if ($booking_id && $status) {
        $stmt = $db->prepare("UPDATE bookings SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $status,
            ':id' => $booking_id
        ]);
        $message = "Status booking berhasil diperbarui.";
    } else {
        $message = "Data tidak valid. Gagal memperbarui status.";
    }
}

// Ambil semua data booking
$bookings = $db->query("SELECT b.id, b.user_id, b.kosan_id, b.status, b.created_at,
                               u.username AS user_name,
                               k.nama AS kosan_name
                        FROM bookings b
                        JOIN users u ON b.user_id = u.id
                        JOIN kosan k ON b.kosan_id = k.id
                        ORDER BY b.created_at DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Booking - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { margin-bottom: 15px; color: green; }
        .form-inline { display: flex; align-items: center; gap: 10px; }
        .form-inline select, .form-inline button {
            padding: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <h1>Kelola Booking</h1>

    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pengguna</th>
                <th>Nama Kosan</th>
                <th>Status</th>
                <th>Tanggal Booking</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($bookings) === 0): ?>
                <tr><td colspan="6">Tidak ada data booking.</td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= $booking['id'] ?></td>
                        <td><?= htmlspecialchars($booking['user_name']) ?></td>
                        <td><?= htmlspecialchars($booking['kosan_name']) ?></td>
                        <td><?= htmlspecialchars($booking['status']) ?></td>
                        <td><?= htmlspecialchars($booking['created_at']) ?></td>
                        <td>
                            <form method="POST" class="form-inline">
                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                <select name="status" required>
                                    <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="approved" <?= $booking['status'] === 'approved' ? 'selected' : '' ?>>Disetujui</option>
                                    <option value="rejected" <?= $booking['status'] === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                                </select>
                                <button type="submit" name="update_booking">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
