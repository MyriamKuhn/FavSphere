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
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
  //On instancie le Middleware
  $jwtMiddleware = new JwtMiddleware();

  // Vérifier le token JWT
  $decoded = $jwtMiddleware->checkJWT();

  //On vérifie la méthode HTTP
  if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    // On obtient les données envoyées en JSON
    $data = json_decode(file_get_contents("php://input"));

    // On vérifie que les données nécessaires sont présentes et non vides
    if (!empty($data->id)) {

      // Sécurisation des données reçues
      $id = htmlspecialchars(trim($data->id));

      // Validation de l'ID
      if (!filter_var($id, FILTER_VALIDATE_INT)) {
        http_response_code(400);
        echo json_encode(["message" => "Données incorrectes ou manquantes."]);
        exit;
      }

      // Connexion à la base de données
      $db = Database::getInstance()->getConnection();

      // Instanciation de la classe Category
      $category = new Category($db);

      // On vérifie si la catégorie existe
      if (!$category->categoryExists($id)) {
        http_response_code(404);
        echo json_encode(["message" => "La catégorie n'existe pas."]);
        exit;
      }

      // On hydrate l'objet
      $category->id = $id;

      // On tente de supprimer la catégorie
      if ($category->deleteCategory()) {
        http_response_code(200);
        echo json_encode(["message" => "La catégorie a été supprimée avec succès."]);
      } else {
        http_response_code(503);
        echo json_encode(["message" => "Impossible de supprimer la catégorie."]);
      }
    } else {
      http_response_code(400);
      echo json_encode(["message" => "Données incorrectes ou manquantes."]);
    }
  } else {
    http_response_code(405);
    echo json_encode(["message" => "Méthode non autorisée"]);
  }

} catch (Throwable $t) {
  // Capture d'erreurs générales (problèmes d'exécution, erreurs non gérées)
  http_response_code(500);  
  echo json_encode(["message" => "Erreur interne du serveur", "error" => $t->getMessage()]);
} catch (Exception $e) {
  // Si une exception est levée, par exemple JWT invalide ou absent
  http_response_code(401);  
  echo json_encode(["message" => "Accès refusé. " . $e->getMessage()]);
}
      
    