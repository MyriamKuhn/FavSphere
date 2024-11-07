<?php

// Chargement de l'autoloader
require_once __DIR__.'/../Autoload.php';
Autoload::register();

use Models\Category;
use config\Database;
use Middleware\JwtMiddleware;

//Headers requis pour le retour HTTP
header("Access-Control-Allow-Origin: " . $_SERVER['SERVER_NAME']);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
  //On instancie le Middleware
  $jwtMiddleware = new JwtMiddleware();

  // Vérifier le token JWT
  $decoded = $jwtMiddleware->checkJWT();

  //On vérifie la méthode HTTP
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // On obtient les données envoyées en JSON
    $data = json_decode(file_get_contents("php://input"));

    // On vérifie que les données nécessaires sont présentes
    if (!empty($data->name) && !empty($data->color)) {

      // Sécurisation des données reçues
      $name = htmlspecialchars(trim($data->name));
      $color = trim($data->color);

      // Validation de la longueur du nom 
      if (strlen($name) < 3 || strlen($name) > 100) {
        http_response_code(400);
        echo json_encode(["message" => "Données incorrectes ou manquantes."]);
        exit;
      }

      // Validation du format de la couleur avec une expression régulière
      if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        http_response_code(400);
        echo json_encode(["message" => "Données incorrectes ou manquantes."]);
        exit;
      }

      // Connexion à la base de données
      $db = Database::getInstance()->getConnection();

      // On instancie l'objet Category
      $category = new Category($db);

      // On hydrate l'objet
      $category->name = $name;
      $category->color = $color;

      // On tente d'ajouter la catégorie à la base de données
      if ($category->addCategory()) {
        // Envoi de la réponse avec un code 201 (Created)
        http_response_code(201);  
        echo json_encode(["message" => "Catégorie ajoutée avec succès"]);
      } else {
        // Si l'ajout échoue
        http_response_code(500);  // Code 500 Internal Server Error
        echo json_encode(["message" => "Erreur interne du serveur. Impossible d'ajouter la catégorie"]);
      }
    } else {
      // Si les données sont manquantes
      http_response_code(400);  
      echo json_encode(["message" => "Données incorrectes ou manquantes."]);
    }
  } else {
    // Si la méthode HTTP n'est pas POST
    http_response_code(405);  
    echo json_encode(["message" => "Méthode non autorisée"]);
  }

} catch (Throwable $t) {
  // Capture d'erreurs générales (problèmes d'exécution, erreurs non gérées)
  http_response_code(500);  // Code 500 Internal Server Error
  echo json_encode(["message" => "Erreur interne du serveur", "error" => $t->getMessage()]);
} catch (Exception $e) {
  // Si une exception est levée, par exemple JWT invalide ou absent
  http_response_code(401);  
  echo json_encode(["message" => "Accès refusé. " . $e->getMessage()]);
}