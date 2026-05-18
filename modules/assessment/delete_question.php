<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_check.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['id'])) {
    $stmt = $pdo->prepare("UPDATE quiz_questions SET status = 0 WHERE id = ?");
    $status = $stmt->execute([$data['id']]);
    echo json_encode(['success' => $status, 'message' => 'Question successfully scrubbed from structural indexes.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Missing database node targeting vector parameters.']);
}