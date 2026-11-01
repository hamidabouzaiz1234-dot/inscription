<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';

use Cloudinary\Cloudinary;

header('Content-Type: application/json; charset=utf-8');

function reponse(bool $ok, string $message, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') reponse(false, 'Méthode non autorisée', 405);

$nom    = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$ville  = trim($_POST['ville'] ?? '');

if ($nom === '' || $prenom === '' || $ville === '') reponse(false, 'Tous les champs sont obligatoires.', 400);

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) reponse(false, 'Photo invalide.', 400);
if ($_FILES['photo']['size'] > 5 * 1024 * 1024) reponse(false, 'Photo trop grande (max 5 Mo).', 400);

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['photo']['tmp_name']);
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) reponse(false, 'Format non autorisé.', 400);

try {
    $cloudinary = new Cloudinary(getenv('CLOUDINARY_URL'));
    $upload = $cloudinary->uploadApi()->upload($_FILES['photo']['tmp_name'], ['folder' => 'stagiaires']);
    $url = $upload['secure_url'];

    $pdo = getDatabase();
    $stmt = $pdo->prepare('INSERT INTO stagiaires (nom, prenom, ville, photo) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nom, $prenom, $ville, $url]);

    reponse(true, 'Stagiaire inscrit avec succès.');
} catch (Throwable $e) {
    error_log($e->getMessage());
    reponse(false, 'Erreur serveur.', 500);
}