<?php
session_start();
require_once 'includes/functions.php';

// Check if user is admin (simple check for demo)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$db = getConnection();
$message = "";
$messageType = "";

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_booking'])) {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $booking_status = filter_input(INPUT_POST, 'booking_status', FILTER_SANITIZE_STRING);
    $payment_status = filter_input(INPUT_POST, 'payment_status', FILTER_SANITIZE_STRING);

    if ($booking_id && $booking_status) {
        try {
            $sql = "UPDATE bookings SET booking_status = :booking_status";
            $params = [':booking_status' => $booking_status, ':id' => $booking_id];
            
            if ($payment_status) {
                $sql .= ", payment_status = :payment_status";
                $params[':payment_status'] = $payment_status;
            }
            
            $sql .= ", updated_at = NOW() WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $message = "Status booking berhasil diperbarui.";
            $messageType = "success";
            
            // Log activity
            log_activity($_SESSION['user']['id'], 'update_booking_status', "Updated booking #{$booking_id} to {$booking_status}");
            
        } catch (Exception $e) {
            $message = "Gagal memperbarui status: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "Data tidak valid. Gagal memperbarui status.";
        $messageType = "error";
    }
}

// Handle booking deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) {
    $booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    
    if ($booking_id) {
        try {
            $stmt = $db->prepare("DELETE FROM bookings WHERE id = :id");
            $stmt->execute([':id' => $booking_id]);
            
            $message = "Booking berhasil dihapus.";
            $messageType = "success";
            
            log_activity($_SESSION['user']['id'], 'delete_booking', "Deleted booking #{$booking_id}");
            
        } catch (Exception $e) {
            $message = "Gagal menghapus booking: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with filters
$sql = "SELECT 
            b.id, 
            b.user_id, 
            b.kos_id, 
            b.booking_code,
            b.full_name,
            b.email,
            b.phone,
            b.check_in_date,
            b.duration_months,
            b.total_price,
            b.admin_fee,
            b.payment_method,
            b.booking_status,
            b.payment_status,
            b.notes,
            b.created_at,
            b.updated_at,
            COALESCE(u.name, 'User tidak ditemukan') AS user_name,
            COALESCE(k.name, 'Kos tidak ditemukan') AS kos_name,
            COALESCE(k.address, '') AS kos_address,
            COALESCE(l.city, '') AS kos_city
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN kos k ON b.kos_id = k.id
        LEFT JOIN locations l ON k.location_id = l.id
        WHERE 1=1";

$params = [];

if ($status_filter) {
    $sql .= " AND b.booking_status = :status";
    $params[':status'] = $status_filter;
}

if ($date_from) {
    $sql .= " AND DATE(b.created_at) >= :date_from";
    $params[':date_from'] = $date_from;
}

if ($date_to) {
    $sql .= " AND DATE(b.created_at) <= :date_to";
    $params[':date_to'] = $date_to;
}

if ($search_term) {
    $sql .= " AND (b.booking_code LIKE :search OR b.full_name LIKE :search OR b.email LIKE :search OR k.name LIKE :search)";
    $params[':search'] = "%{$search_term}%";
}

$sql .= " ORDER BY b.created_at DESC";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = "Error fetching bookings: " . $e->getMessage();
    $messageType = "error";
    $bookings = [];
}

// Get booking statistics
try {
    $stats_sql = "SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
                    SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
                    SUM(CASE WHEN payment_status = 'paid' THEN total_price ELSE 0 END) as total_revenue
                  FROM bookings";
    $stats = $db->query($stats_sql)->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $stats = [
        'total_bookings' => 0,
        'pending_count' => 0,
        'confirmed_count' => 0,
        'cancelled_count' => 0,
        'total_revenue' => 0
    ];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking - Admin | TemanKosan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: #333;
        }

        /* Header */
        .admin-header {
            background: linear-gradient(135deg, #ff69b4, #00c851);
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .admin-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .admin-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .stat-card.total { border-color: rgba(0, 200, 81, 0.2); }
        .stat-card.pending { border-color: rgba(255, 193, 7, 0.2); }
        .stat-card.confirmed { border-color: rgba(0, 200, 81, 0.2); }
        .stat-card.cancelled { border-color: rgba(220, 53, 69, 0.2); }
        .stat-card.revenue { border-color: rgba(255, 105, 180, 0.2); }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
        }

        /* Message Alert */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .alert.success {
            background: linear-gradient(135deg, rgba(0, 200, 81, 0.1), rgba(0, 200, 81, 0.05));
            color: #00a844;
            border: 2px solid rgba(0, 200, 81, 0.2);
        }

        .alert.error {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
            color: #dc3545;
            border: 2px solid rgba(220, 53, 69, 0.2);
        }

        /* Filters */
        .filters-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .form-group input,
        .form-group select {
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00c851;
            box-shadow: 0 0 0 3px rgba(0, 200, 81, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00c851, #00a844);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 200, 81, 0.3);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 2px solid #e0e0e0;
        }

        .btn-secondary:hover {
            background: #e9ecef;
        }

        /* Booking Table */
        .bookings-section {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        .section-header {
            background: linear-gradient(135deg, rgba(255, 105, 180, 0.1), rgba(0, 200, 81, 0.1));
            padding: 1.5rem 2rem;
            border-bottom: 2px solid rgba(0, 200, 81, 0.1);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
        }

        .table-container {
            overflow-x: auto;
        }

        .bookings-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bookings-table th,
        .bookings-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .bookings-table th {
            background: linear-gradient(135deg, rgba(255, 105, 180, 0.05), rgba(0, 200, 81, 0.05));
            font-weight: 700;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .bookings-table tr:hover {
            background: linear-gradient(135deg, rgba(255, 105, 180, 0.02), rgba(0, 200, 81, 0.02));
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(255, 193, 7, 0.1));
            color: #856404;
        }

        .status-confirmed {
            background: linear-gradient(135deg, rgba(0, 200, 81, 0.2), rgba(0, 200, 81, 0.1));
            color: #155724;
        }

        .status-cancelled {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.2), rgba(220, 53, 69, 0.1));
            color: #721c24;
        }

        .status-paid {
            background: linear-gradient(135deg, rgba(0, 200, 81, 0.2), rgba(0, 200, 81, 0.1));
            color: #155724;
        }

        .status-unpaid {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.2), rgba(220, 53, 69, 0.1));
            color: #721c24;
        }

        .booking-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        .btn-update {
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            color: white;
            border: none;
        }

        .btn-update:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(255, 105, 180, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            border: none;
        }

        .btn-delete:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.4);
        }

        .booking-code {
            font-family: monospace;
            background: linear-gradient(135deg, rgba(0, 200, 81, 0.1), rgba(0, 200, 81, 0.05));
            padding: 0.2rem 0.5rem;
            border-radius: 5px;
            font-weight: 600;
        }

        .price {
            font-weight: 700;
            background: linear-gradient(135deg, #00c851, #00a844);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Modal for Update/Delete */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            margin-bottom: 1.5rem;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        /* No data state */
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .no-data-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .admin-header h1 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .filter-buttons {
                flex-direction: column;
            }

            .bookings-table {
                font-size: 0.9rem;
            }

            .booking-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="admin-header">
        <h1>🏠 Kelola Booking</h1>
        <p>Admin Panel - TemanKosan</p>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">📊</div>
                <div class="stat-number"><?php echo number_format($stats['total_bookings']); ?></div>
                <div class="stat-label">Total Booking</div>
            </div>
            
            <div class="stat-card pending">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?php echo number_format($stats['pending_count']); ?></div>
                <div class="stat-label">Pending</div>
            </div>
            
            <div class="stat-card confirmed">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo number_format($stats['confirmed_count']); ?></div>
                <div class="stat-label">Dikonfirmasi</div>
            </div>
            
            <div class="stat-card cancelled">
                <div class="stat-icon">❌</div>
                <div class="stat-number"><?php echo number_format($stats['cancelled_count']); ?></div>
                <div class="stat-label">Dibatalkan</div>
            </div>
            
            <div class="stat-card revenue">
                <div class="stat-icon">💰</div>
                <div class="stat-number">Rp <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>

        <!-- Message Alert -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $messageType; ?>">
                <span><?php echo $messageType === 'success' ? '✅' : '⚠️'; ?></span>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="search">🔍 Cari Booking</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Kode booking, nama, email, kos...">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">📋 Status Booking</label>
                        <select id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_from">📅 Dari Tanggal</label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_to">📅 Sampai Tanggal</label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">
                        🔍 Filter Data
                    </button>
                    <a href="manage-bookings.php" class="btn btn-secondary">
                        🔄 Reset Filter
                    </a>
                </div>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="bookings-section">
            <div class="section-header">
                <h2 class="section-title">📋 Daftar Booking (<?php echo count($bookings); ?> hasil)</h2>
            </div>
            
            <?php if (count($bookings) > 0): ?>
                <div class="table-container">
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>Kode Booking</th>
                                <th>Pelanggan</th>
                                <th>Kos</th>
                                <th>Check-in</th>
                                <th>Durasi</th>
                                <th>Total Harga</th>
                                <th>Status Booking</th>
                                <th>Status Pembayaran</th>
                                <th>Tanggal Booking</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <span class="booking-code"><?php echo htmlspecialchars($booking['booking_code']); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($booking['email']); ?></small><br>
                                        <small><?php echo htmlspecialchars($booking['phone']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['kos_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($booking['kos_address']); ?></small>
                                    </td>
                                    <td><?php echo format_date($booking['check_in_date'], 'd M Y'); ?></td>
                                    <td><?php echo $booking['duration_months']; ?> bulan</td>
                                    <td>
                                        <span class="price">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                            <?php echo ucfirst($booking['booking_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $booking['payment_status'] ?? 'unpaid'; ?>">
                                            <?php echo ucfirst($booking['payment_status'] ?? 'unpaid'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($booking['created_at'], 'd M Y H:i'); ?></td>
                                    <td>
                                        <div class="booking-actions">
                                            <button type="button" class="btn btn-update btn-sm" onclick="openUpdateModal(<?php echo $booking['id']; ?>, '<?php echo $booking['booking_status']; ?>', '<?php echo $booking['payment_status'] ?? 'unpaid'; ?>')">
                                                ✏️ Edit
                                            </button>
                                            <button type="button" class="btn btn-delete btn-sm" onclick="openDeleteModal(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['booking_code']); ?>')">
                                                🗑️ Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">📋</div>
                    <h3>Tidak ada data booking</h3>
                    <p>Belum ada booking yang sesuai dengan filter yang diterapkan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Update Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">✏️ Update Status Booking</h3>
            </div>
            <form method="POST" id="updateForm">
                <input type="hidden" name="booking_id" id="updateBookingId">
                
                <div class="form-group">
                    <label for="updateBookingStatus">Status Booking</label>
                    <select name="booking_status" id="updateBookingStatus" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Dikonfirmasi</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="updatePaymentStatus">Status Pembayaran</label>
                    <select name="payment_status" id="updatePaymentStatus">
                        <option value="unpaid">Belum Dibayar</option>
                        <option value="paid">Sudah Dibayar</option>
                        <option value="refunded">Refund</option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('updateModal')">
                        ❌ Batal
                    </button>
                    <button type="submit" name="update_booking" class="btn btn-primary">
                        ✅ Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">🗑️ Hapus Booking</h3>
            </div>
            <p>Apakah Anda yakin ingin menghapus booking <strong id="deleteBookingCode"></strong>?</p>
            <p><small>Tindakan ini tidak dapat dibatalkan.</small></p>
            
            <form method="POST" id="deleteForm">
                <input type="hidden" name="booking_id" id="deleteBookingId">
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">
                        ❌ Batal
                    </button>
                    <button type="submit" name="delete_booking" class="btn btn-delete">
                        🗑️ Hapus Booking
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUpdateModal(bookingId, bookingStatus, paymentStatus) {
            document.getElementById('updateBookingId').value = bookingId;
            document.getElementById('updateBookingStatus').value = bookingStatus;
            document.getElementById('updatePaymentStatus').value = paymentStatus;
            document.getElementById('updateModal').classList.add('show');
        }

        function openDeleteModal(bookingId, bookingCode) {
            document.getElementById('deleteBookingId').value = bookingId;
            document.getElementById('deleteBookingCode').textContent = bookingCode;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('show');
            }
        });

        // Escape key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    openModal.classList.remove('show');
                }
            }
        });
    </script>
</body>
</html>
