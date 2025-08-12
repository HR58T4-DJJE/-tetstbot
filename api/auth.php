<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register($name, $email, $password) {
        try {
            // Validate input
            $this->validateRegistrationInput($name, $email, $password);

            // Check if user already exists
            if ($this->userExists($email)) {
                return [
                    'success' => false,
                    'message' => 'User with this email already exists'
                ];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $this->db->query($sql, [$name, $email, $hashedPassword]);

            $userId = $this->db->lastInsertId();

            // Get user data
            $user = $this->getUserById($userId);

            return [
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => $user,
                    'token' => $this->generateToken($user)
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }

    public function login($email, $password) {
        try {
            // Validate input
            if (empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Email and password are required'
                ];
            }

            // Get user by email
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }

            // Remove password from response
            unset($user['password']);

            return [
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $this->generateToken($user)
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ];
        }
    }

    public function getUserProfile($userId) {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Get user stats
            $stats = $this->getUserStats($userId);

            // Remove password from response
            unset($user['password']);

            return [
                'success' => true,
                'data' => [
                    'user' => $user,
                    'stats' => $stats
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get user profile: ' . $e->getMessage()
            ];
        }
    }

    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['name', 'avatar'];
            $updateFields = [];
            $params = [];

            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields) && !empty($value)) {
                    $updateFields[] = "$field = ?";
                    $params[] = $value;
                }
            }

            if (empty($updateFields)) {
                return [
                    'success' => false,
                    'message' => 'No valid fields to update'
                ];
            }

            $params[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $this->db->query($sql, $params);

            // Get updated user
            $user = $this->getUserById($userId);
            unset($user['password']);

            return [
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ];
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get user
            $user = $this->getUserById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }

            // Validate new password
            if (strlen($newPassword) < 6) {
                return [
                    'success' => false,
                    'message' => 'New password must be at least 6 characters long'
                ];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password
            $sql = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $this->db->query($sql, [$hashedPassword, $userId]);

            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage()
            ];
        }
    }

    private function validateRegistrationInput($name, $email, $password) {
        if (empty($name) || strlen($name) < 2) {
            throw new Exception('Name must be at least 2 characters long');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Valid email is required');
        }

        if (empty($password) || strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long');
        }
    }

    private function userExists($email) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    private function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    private function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        return $stmt->fetch();
    }

    private function getUserStats($userId) {
        // Get image count
        $imageSql = "SELECT COUNT(*) as count FROM images WHERE user_id = ?";
        $imageStmt = $this->db->query($imageSql, [$userId]);
        $imageCount = $imageStmt->fetch()['count'];

        // Get total views
        $viewsSql = "SELECT SUM(views) as total FROM images WHERE user_id = ?";
        $viewsStmt = $this->db->query($viewsSql, [$userId]);
        $totalViews = $viewsStmt->fetch()['total'] ?? 0;

        // Get total downloads
        $downloadsSql = "SELECT SUM(downloads) as total FROM images WHERE user_id = ?";
        $downloadsStmt = $this->db->query($downloadsSql, [$userId]);
        $totalDownloads = $downloadsStmt->fetch()['total'] ?? 0;

        // Get total likes
        $likesSql = "SELECT COUNT(*) as count FROM likes l 
                     JOIN images i ON l.image_id = i.id 
                     WHERE i.user_id = ?";
        $likesStmt = $this->db->query($likesSql, [$userId]);
        $totalLikes = $likesStmt->fetch()['count'];

        return [
            'images_count' => $imageCount,
            'total_views' => $totalViews,
            'total_downloads' => $totalDownloads,
            'total_likes' => $totalLikes
        ];
    }

    private function generateToken($user) {
        // Simple token generation (in production, use JWT)
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'exp' => time() + (60 * 60 * 24 * 7) // 7 days
        ];
        
        return base64_encode(json_encode($payload));
    }

    public function validateToken($token) {
        try {
            $payload = json_decode(base64_decode($token), true);
            
            if (!$payload || !isset($payload['user_id']) || !isset($payload['exp'])) {
                return false;
            }

            if ($payload['exp'] < time()) {
                return false;
            }

            return $payload['user_id'];

        } catch (Exception $e) {
            return false;
        }
    }
}

// Handle requests
$auth = new Auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'register':
            $name = $input['name'] ?? '';
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            
            $result = $auth->register($name, $email, $password);
            break;

        case 'login':
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            
            $result = $auth->login($email, $password);
            break;

        case 'profile':
            $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            
            $userId = $auth->validateToken($token);
            if (!$userId) {
                $result = ['success' => false, 'message' => 'Invalid token'];
            } else {
                $result = $auth->getUserProfile($userId);
            }
            break;

        case 'update':
            $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            
            $userId = $auth->validateToken($token);
            if (!$userId) {
                $result = ['success' => false, 'message' => 'Invalid token'];
            } else {
                $result = $auth->updateProfile($userId, $input);
            }
            break;

        case 'change-password':
            $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            
            $userId = $auth->validateToken($token);
            if (!$userId) {
                $result = ['success' => false, 'message' => 'Invalid token'];
            } else {
                $currentPassword = $input['current_password'] ?? '';
                $newPassword = $input['new_password'] ?? '';
                $result = $auth->changePassword($userId, $currentPassword, $newPassword);
            }
            break;

        default:
            $result = ['success' => false, 'message' => 'Invalid action'];
    }

    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>