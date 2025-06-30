<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
     
header('Content-Type: application/json');
            
      
include 'config.php';
include 'index/header.php';
class NotificationMarker {
    private $conn;
    private $logFile = 'mark_read_debug.log';
    private $userId;   
    public $response = ['success' => false, 'message' => '', 'affected_rows' => 0];
      
    public function __construct($conn) {
        $this->conn = $conn;
        $this->log("Mark all as read process started");
    }

    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    public function setUser($userId) {
        if (!$userId) {
            throw new Exception("User not authenticated.");
        }
        $this->userId = $userId;
        $this->log("User ID set to {$this->userId}");
    }

    public function checkConnection() {
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
        $this->log("Database connection successful");
    }

    public function checkUnread() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE is_read = 0 AND user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed in checkUnread: " . $this->conn->error);
        }
        $stmt->bind_param("i", $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Execution failed in checkUnread: " . $stmt->error);
        }
        $row = $result->fetch_assoc();
        $unread = $row['unread_count'] ?? 0;
        $this->log("Found {$unread} unread notifications");
        return $unread;
    }

    public function markAsRead() {
        $this->log("Preparing to update notifications for user ID: {$this->userId}");
        // Mark as read for both user-specific and global notifications
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1, status = 'read' WHERE is_read = 0 AND (user_id = ? OR user_id IS NULL)");
        if (!$stmt) {
            throw new Exception("Prepare failed in markAsRead: " . $this->conn->error);
        }
        $stmt->bind_param("i", $this->userId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed in markAsRead: " . $stmt->error);
        }
        if ($stmt->affected_rows > 0) {
            $this->response['success'] = true;
            $this->response['message'] = "Marked {$stmt->affected_rows} notifications as read";
            $this->response['affected_rows'] = $stmt->affected_rows;
            $this->log("Updated {$stmt->affected_rows} notifications");
        } else {
            $this->response['success'] = true;
            $this->response['message'] = "No unread notifications found";
            $this->log("No notifications updated");
        }
    }

    public function closeConnection() {
        $this->conn->close();
        $this->log("Database connection closed");
    }

    public function getResponse() {
        return $this->response;
    }
}

// ==== Main Process ====
$response = ['success' => false, 'message' => 'Unknown error occurred'];

try {
    // Check for logged in user
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not logged in.");
    }

    // Mark a single notification as read if notification_id is provided
    if (isset($_POST['notification_id'])) {
        $notification_id = intval($_POST['notification_id']);
        // Allow marking global notifications as read too
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1, status = 'read' WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $response = ['success' => true, 'message' => 'Notification marked as read'];
        } else {
            $response = ['success' => false, 'message' => 'No notification updated'];
        }
        $stmt->close();
        echo json_encode($response);
        exit;
    }

    file_put_contents('mark_read_debug.log', "[DEBUG] Session user_id: " . $_SESSION['user_id'] . "\n", FILE_APPEND);

    $marker = new NotificationMarker($conn);
    $marker->setUser($_SESSION['user_id']);
    $marker->checkConnection();

    $unreadCount = $marker->checkUnread();
    if ($unreadCount > 0) {
        $marker->markAsRead();
    } else {
        $marker->response['success'] = true;
        $marker->response['message'] = "No unread notifications found";
    }

    $marker->closeConnection();
    $response = $marker->getResponse();

} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
    file_put_contents('mark_read_debug.log', "[" . date('Y-m-d H:i:s') . "] " . $error_message . "\n", FILE_APPEND);
    $response = ['success' => false, 'message' => $error_message];
}

// Return JSON response
echo json_encode($response);
exit;
