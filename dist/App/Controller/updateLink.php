<?php

// Chargement de l'autoloader
require_once __DIR__.'/../Autoload.php';
Autoload::register();

use Models\Link;
use Models\Category;
use config\Database;
use Middleware\JwtMiddleware;

//Headers requis pour le retour HTTP
header("Access-Control-Allow-Origin: " . $_SERVER['SERVER_NAME']);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
  //On instancie le Middleware
  $jwtMiddleware = new JwtMiddleware();

  // Vérifier le token JWT
  $decoded = $jwtMiddleware->checkJWT();

  //On vérifie la méthode HTTP
  if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    // On récupère l'ID du user dans le token
    $userId = $decoded->user['id'];

    // Verification du userId
    if (!filter_var($userId, FILTER_VALIDATE_INT)) {
      http_response_code(400);
      echo json_encode(["message" => "Données incorrectes ou manquantes."]);
      exit;
    }

    // On obtient les données envoyées en JSON
    $data = json_decode(file_get_contents("php://input"));

    // On vérifie que les données nécessaires sont présentes
    if (!empty($data->id) &&!empty($data->url) && !empty($data->title) && !empty($data->description) && !empty($data->fk_category_id)) {

      // Sécurisation des données reçues
      $id = htmlspecialchars(trim($data->id));
      $url = htmlspecialchars(trim($data->url));
      $title = htmlspecialchars(trim($data->title));
      $description = htmlspecialchars(trim($data->description));
      $fk_category_id = intval($data->fk_category_id);

      // Validation de l'ID
      if (!filter_var($id, FILTER_VALIDATE_INT)) {
        http_response_code(400);
        echo json_encode(["message" => "Données incorrectes ou manquantes."]);
        exit;
      }

      // Validation de la longueur de l'URL
      if (strlen($url) < 3 || strlen($url) > 255) {
        http_response_code(400);
        echo json_encode(["message" => "Données incorrectes ou manquantes."]);
        exit;
      }

      // Validation du format de l'URL
      if (!filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode(["message" => "Données incorrectes ou manquantes."]);
        exit;
      }

      // Validation de la longueur du titre
      if (strlen($title) < 3 || strlen($title) > 100) {
        http_response_code(400);
        echo json_encode(["message" => "Données incorrectes ou manquantes."]);
        exit;
      }

      // Validation de la longueur de la description
      if (strlen($description) < 3) {
        http_response_code(400);
        echo json_encode(["message" => "Données incorrectes ou manquantes."]);
        exit;
      }

      // Validation de l'ID de la catégorie
      if (!filter_var($fk_category_id, FILTER_VALIDATE_INT)) {
        http_response_code(400);
        echo json_encode(["message" => "Données incorrectes ou manquantes."]);
        exit;
      }

      // Connexion à la base de données
      $db = Database::getInstance()->getConnection();

      // On vérifie si la catégorie existe
      $category = new Category($db);
      if (!$category->categoryExists($fk_category_id)) {
        http_response_code(400);
        echo json_encode(["message" => "Catégorie inexistante"]);
        exit;
      }

      // On instancie l'objet Link
      $link = new Link($db);

      // On vérifie si le lien existe
      if ($link->linkExists($id)) {

        // On hydrate l'objet
        $link->id = $id;
        $link->url = $url;
        $link->title = $title;
        $link->description = $description;
        $link->fk_category_id = $fk_category_id;
        $link->fk_user_id = $userId;

        // On tente de mettre à jour le lien dans la base de données
        if ($link->updateLink()) {
          // Envoi de la réponse avec un code 200 
          http_response_code(200);  
          echo json_encode(["message" => "Lien modifié avec succès"]);
        } else {
          // Si l'ajout échoue
          http_response_code(500);  
          echo json_encode(["message" => "Erreur interne du serveur. Impossible de modifier le lien."]);
        }
      } else {
        // Si le lien n'existe pas
        http_response_code(404);  
        echo json_encode(["message" => "Le lien n'existe pas"]); 
      }
    } else {
      // Si les données sont manquantes
      http_response_code(400);  
      echo json_encode(["message" => "Données incorrectes ou manquantes."]);
    }
  } else {
    // Si la méthode HTTP n'est pas PUT
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