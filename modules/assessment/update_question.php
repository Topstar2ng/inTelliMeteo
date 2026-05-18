<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_check.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['id']) && !empty($data['question_text'])) {
    $stmt = $pdo->prepare("UPDATE quiz_questions SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ? WHERE id = ?");
    $status = $stmt->execute([
        $data['question_text'], $data['option_a'], $data['option_b'], 
        $data['option_c'], $data['option_d'], $data['correct_option'], $data['id']
    ]);
    
    echo json_encode(['success' => $status, 'message' => $status ? 'Item adjusted successfully.' : 'No modifications executed.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Malformed validation vectors payload.']);
}