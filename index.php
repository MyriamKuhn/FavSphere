<?php
// Affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/error.log');

// Sécurisation du cookie de session avec httpOnly
session_set_cookie_params([
	'lifetime' => 3600,
	'path' => '/',
	'domain' => $_SERVER['SERVER_NAME'],
	'secure' => true,
	'httponly' => true,
	'samesite' => 'Strict',
]);

// On démarre la session
session_start();

// On inclut le fichier de configuration
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fonction de vérification CSRF
function verifyCsrfToken() {
  // Si la requête provient de Swagger 
  if (strpos($_SERVER['REQUEST_URI'], '/app/swagger') !== false) {
    return true; // On ne vérifie pas le token CSRF
  }
  // Sinon, vérifier le token CSRF comme d'habitude
  $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
  return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// On détermine le chemin actuel de la requête
$requestUri = $_SERVER['REQUEST_URI'];

// Vérification CSRF pour les requêtes POST, PUT et DELETE
if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE']) && !verifyCsrfToken()) {
  http_response_code(403);
  echo json_encode(["message" => "Invalid CSRF token"]);
  exit;
}

// Si la requête commence par /app, c'est une requête API
if (strpos($requestUri, '/app') === 0) {
  require_once 'App/index.php'; // Le fichier qui gère les requêtes API
} else {
  require_once 'site/index.html'; // Le fichier qui gère les pages du site
}