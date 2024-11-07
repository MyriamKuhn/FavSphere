<?php

// Chargement de l'autoloader
require_once __DIR__.'/../Autoload.php';
Autoload::register();

use Models\Category;
use config\Database;
use Middleware\JwtMiddleware;

//Headers requis pour le retour HTTP
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
  //On instancie le Middleware
  $jwtMiddleware = new JwtMiddleware();

  // Vérifier le token JWT
  $decoded = $jwtMiddleware->checkJWT();

  // Vous pouvez maintenant utiliser les données décodées
  // par exemple, $decoded->userId et $decoded->username

  //On vérifie la méthode HTTP
  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    //On instancie la base de données
    $db = Database::getInstance()->getConnection();

    //On instancie les catégories
    $category = new Category($db);

    //On récupère les catégories
    $categories = $category->getCategories();
    
    // Vérification s'il y a au moins une catégorie
    if ($categories) {
      // Envoi du code réponse 200 OK
      http_response_code(200);
      //On encode en json et on envoie
      echo json_encode(['categories' => $categories]);
    } else {
      //On envoie le code réponse 404 Not found
      http_response_code(404);
      echo json_encode(["message" => "Aucune catégorie trouvée"]);
    }
  } else {
    // Méthode HTTP non autorisée
    http_response_code(405);
    echo json_encode(["message" => "La méthode n'est pas autorisée"]);
  }
} catch (Exception $e) {
  // Si le JWT est invalide ou absent
  http_response_code(401); // Unauthorized
  echo json_encode(["message" => "Accès refusé. " . $e->getMessage()]);
}