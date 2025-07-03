<?php
require_once 'config/database.php';

class Favorite {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Add to favorites
    public function addFavorite($userId, $kosId) {
        $this->db->query('INSERT IGNORE INTO favorites (user_id, kos_id) VALUES (:user_id, :kos_id)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':kos_id', $kosId);
        
        return $this->db->execute();
    }

    // Remove from favorites
    public function removeFavorite($userId, $kosId) {
        $this->db->query('DELETE FROM favorites WHERE user_id = :user_id AND kos_id = :kos_id');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':kos_id', $kosId);
        
        return $this->db->execute();
    }

    // Check if kos is favorited
    public function isFavorite($userId, $kosId) {
        $this->db->query('SELECT id FROM favorites WHERE user_id = :user_id AND kos_id = :kos_id');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':kos_id', $kosId);
        
        return $this->db->rowCount() > 0;
    }

    // Get user favorites
    public function getUserFavorites($userId) {
        $this->db->query('SELECT k.*, f.created_at as favorited_at,
                         (SELECT image_path FROM kos_images WHERE kos_id = k.id AND is_primary = 1 LIMIT 1) as primary_image
                         FROM favorites f 
                         LEFT JOIN kos k ON f.kos_id = k.id 
                         WHERE f.user_id = :user_id 
                         ORDER BY f.created_at DESC');
        $this->db->bind(':user_id', $userId);
        
        return $this->db->resultSet();
    }
}
?>
