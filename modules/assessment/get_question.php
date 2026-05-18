<?php
require_once '../../includes/db.php';
require_once '../../includes/auth_check.php'; // Ensure user has admin/edit privileges

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE id = ? AND status = 1");
$stmt->execute([$id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($question ?: ['error' => 'Question profile context target not found.']);