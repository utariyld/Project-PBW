<?php
require_once 'models/Kos.php';
require_once 'models/Facility.php';

/**
 * Kos Controller
 * Handles kos-related HTTP requests
 */
class KosController {
    private $kosModel;
    private $facilityModel;

    public function __construct() {
        $this->kosModel = new Kos();
        $this->facilityModel = new Facility();
    }

    /**
     * Display kos listing page
     */
    public function index() {
        try {
            $filters = [
                'location' => $_GET['location'] ?? '',
                'min_price' => $_GET['min_price'] ?? 0,
                'max_price' => $_GET['max_price'] ?? 10000000,
                'type' => $_GET['type'] ?? '',
                'facilities' => $_GET['facilities'] ?? [],
                'sort' => $_GET['sort'] ?? 'newest',
                'limit' => $_GET['limit'] ?? 20
            ];

            $kosList = $this->kosModel->searchKos($filters);
            $facilities = $this->facilityModel->getFacilitiesByCategory();

            include 'views/kos/index.php';
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Display single kos details
     */
    public function show($id) {
        try {
            $kos = $this->kosModel->getKosWithDetails($id);
            
            if (!$kos) {
                header('HTTP/1.0 404 Not Found');
                include 'views/errors/404.php';
                return;
            }

            include 'views/kos/show.php';
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Display create kos form
     */
    public function create() {
        $this->requireAuth(['owner', 'admin']);
        
        try {
            $facilities = $this->facilityModel->getFacilitiesByCategory();
            include 'views/kos/create.php';
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Store new kos
     */
    public function store() {
        $this->requireAuth(['owner', 'admin']);
        
        try {
            $data = $this->validateKosData($_POST);
            $data['owner_id'] = $_SESSION['user']['id'];
            
            $facilities = $_POST['facilities'] ?? [];
            $rules = array_filter($_POST['rules'] ?? []);
            $images = $_POST['images'] ?? [];

            $kosId = $this->kosModel->createKos($data, $facilities, $rules, $images);
            
            $_SESSION['success'] = 'Kos berhasil ditambahkan!';
            header('Location: /kos/' . $kosId);
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /kos/create');
        }
    }

    /**
     * Display edit kos form
     */
    public function edit($id) {
        $this->requireAuth(['owner', 'admin']);
        
        try {
            $kos = $this->kosModel->getKosWithDetails($id);
            
            if (!$kos) {
                header('HTTP/1.0 404 Not Found');
                include 'views/errors/404.php';
                return;
            }

            // Check ownership
            if ($_SESSION['user']['role'] !== 'admin' && $kos['owner_id'] !== $_SESSION['user']['id']) {
                header('HTTP/1.0 403 Forbidden');
                include 'views/errors/403.php';
                return;
            }

            $facilities = $this->facilityModel->getFacilitiesByCategory();
            include 'views/kos/edit.php';
            
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Update kos
     */
    public function update($id) {
        $this->requireAuth(['owner', 'admin']);
        
        try {
            $kos = $this->kosModel->find($id);
            
            if (!$kos) {
                throw new Exception('Kos tidak ditemukan');
            }

            // Check ownership
            if ($_SESSION['user']['role'] !== 'admin' && $kos['owner_id'] !== $_SESSION['user']['id']) {
                throw new Exception('Anda tidak memiliki akses untuk mengubah kos ini');
            }

            $data = $this->validateKosData($_POST);
            $facilities = $_POST['facilities'] ?? [];
            $rules = array_filter($_POST['rules'] ?? []);
            $images = $_POST['images'] ?? [];

            $this->kosModel->updateKos($id, $data, $facilities, $rules, $images);
            
            $_SESSION['success'] = 'Kos berhasil diperbarui!';
            header('Location: /kos/' . $id);
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /kos/' . $id . '/edit');
        }
    }

    /**
     * Delete kos
     */
    public function delete($id) {
        $this->requireAuth(['owner', 'admin']);
        
        try {
            $kos = $this->kosModel->find($id);
            
            if (!$kos) {
                throw new Exception('Kos tidak ditemukan');
            }

            // Check ownership
            if ($_SESSION['user']['role'] !== 'admin' && $kos['owner_id'] !== $_SESSION['user']['id']) {
                throw new Exception('Anda tidak memiliki akses untuk menghapus kos ini');
            }

            $this->kosModel->deleteKos($id);
            
            $_SESSION['success'] = 'Kos berhasil dihapus!';
            header('Location: /dashboard');
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /kos/' . $id);
        }
    }

    /**
     * Search kos via AJAX
     */
    public function search() {
        header('Content-Type: application/json');
        
        try {
            $filters = [
                'location' => $_GET['q'] ?? '',
                'limit' => $_GET['limit'] ?? 10
            ];

            $results = $this->kosModel->searchKos($filters);
            
            echo json_encode([
                'success' => true,
                'data' => $results
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate kos data
     */
    private function validateKosData($data) {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Nama kos harus diisi';
        }

        if (empty($data['address'])) {
            $errors[] = 'Alamat harus diisi';
        }

        if (empty($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
            $errors[] = 'Harga harus berupa angka positif';
        }

        if (empty($data['type']) || !in_array($data['type'], ['putra', 'putri', 'putra-putri'])) {
            $errors[] = 'Tipe kos tidak valid';
        }

        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        return [
            'name' => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
            'address' => trim($data['address']),
            'price' => (int)$data['price'],
            'type' => $data['type'],
            'room_size' => trim($data['room_size'] ?? ''),
            'total_rooms' => (int)($data['total_rooms'] ?? 1),
            'available_rooms' => (int)($data['available_rooms'] ?? 1),
            'is_available' => isset($data['is_available']) ? 1 : 0,
            'is_featured' => isset($data['is_featured']) ? 1 : 0
        ];
    }

    /**
     * Require authentication
     */
    private function requireAuth($roles = []) {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if (!empty($roles) && !in_array($_SESSION['user']['role'], $roles)) {
            header('HTTP/1.0 403 Forbidden');
            include 'views/errors/403.php';
            exit;
        }
    }

    /**
     * Handle errors
     */
    private function handleError($e) {
        error_log($e->getMessage());
        $_SESSION['error'] = 'Terjadi kesalahan sistem';
        include 'views/errors/500.php';
    }
}
?>
