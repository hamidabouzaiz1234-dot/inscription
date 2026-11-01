<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDatabase();
    $stagiaires = $pdo->query('SELECT id, nom, prenom, ville, photo, date_inscription FROM stagiaires ORDER BY id DESC')->fetchAll();
    echo json_encode(['success' => true, 'data' => $stagiaires]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}