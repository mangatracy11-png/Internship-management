<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/db_connect.php';

$action = $_POST['action'] ?? '';

if (!in_array($action, ['assign', 'remove'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

$intern_id = isset($_POST['intern_id']) ? (int)$_POST['intern_id'] : 0;
if (!$intern_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid intern ID']);
    exit;
}

$check_intern = $conn->prepare("SELECT id FROM intern WHERE id = ?");
$check_intern->bind_param('i', $intern_id);
$check_intern->execute();
$intern_result = $check_intern->get_result();

if ($intern_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Intern not found']);
    $check_intern->close();
    $conn->close();
    exit;
}
$check_intern->close();

if ($action === 'assign') {
    $supervisor_id = isset($_POST['supervisor_id']) ? (int)$_POST['supervisor_id'] : 0;
    
    if (!$supervisor_id) {
        echo json_encode(['success' => false, 'message' => 'Please select a supervisor']);
        exit;
    }
    
    $check_sup = $conn->prepare("SELECT id, name FROM users WHERE id = ? AND role = 'supervisor'");
    $check_sup->bind_param('i', $supervisor_id);
    $check_sup->execute();
    $sup_result = $check_sup->get_result();
    
    if ($sup_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Selected supervisor not found or invalid role']);
        $check_sup->close();
        $conn->close();
        exit;
    }
    
    $supervisor = $sup_result->fetch_assoc();
    $check_sup->close();
    
    $stmt = $conn->prepare("UPDATE intern SET supervisor_id = ? WHERE id = ?");
    $stmt->bind_param('ii', $supervisor_id, $intern_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Supervisor assigned successfully',
            'supervisor_name' => $supervisor['name']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
}

else if ($action === 'remove') {
    $stmt = $conn->prepare("UPDATE intern SET supervisor_id = NULL WHERE id = ?");
    $stmt->bind_param('i', $intern_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Supervisor removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $stmt->close();
}

$conn->close();
?>