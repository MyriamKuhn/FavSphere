<?php

// Chargement de l'autoloader
require_once __DIR__.'/../Autoload.php';
Autoload::register();

use Models\Link;
use config\Database;
use Middleware\JwtMiddleware;

//Headers requis pour le retour HTTP
header("Access-Control-Allow-Origin: " . $_SERVER['SERVER_NAME']);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
  //On instancie le Middleware
  $jwtMiddleware = new JwtMiddleware();

  // Vérifier le token JWT
  $decoded = $jwtMiddleware->checkJWT();

  //On vérifie la méthode HTTP
  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // On récupère l'ID du user dans le token
    $userId = $decoded->user['id'];

    // Verification du userId
    if (!filter_var($userId, FILTER_VALIDATE_INT)) {
      http_response_code(400);
      echo json_encode(["message" => "Données incorrectes ou manquantes."]);
      exit;
    }

    //On instancie la base de données
    $db = Database::getInstance()->getConnection();

    //On instancie la classe Link
    $link = new Link($db);

    // On hydrate l'objet Link
    $link->fk_user_id = $userId;

    //On récupère les liens
    $links = $link->getLinks();
    
    // Vérification s'il y a au moins un lien
    if ($links) {
      // Envoi du code réponse 200 OK
      http_response_code(200);
      //On encode en json et on envoie
      echo json_encode(['links' => $links]);
    } else {
      //On envoie le code réponse 404 Not found
      http_response_code(404);
      echo json_encode(["message" => "Aucun lien trouvé"]);
    }
  } else {
    // Méthode HTTP non autorisée
    http_response_code(405);
    echo json_encode(["message" => "La méthode n'est pas autorisée"]);
  }

} catch (Throwable $t) {
  // Si une erreur survient
  http_response_code(500); 
  echo json_encode(["message" => "Erreur interne. " . $t->getMessage()]);
} catch (Exception $e) {
  // Si le JWT est invalide ou absent
  http_response_code(401); 
  echo json_encode(["message" => "Accès refusé. " . $e->getMessage()]);
}