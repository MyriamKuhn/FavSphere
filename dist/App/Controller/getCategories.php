<?php

use Models\Category;
use config\Database;
use Middleware\JwtMiddleware;
use Tools\Utils;

try {
  //On instancie le Middleware
  $jwtMiddleware = new JwtMiddleware();

  // Vérifier le token JWT
  $decoded = $jwtMiddleware->checkJWT();

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
      Utils::sendResponse(200, json_encode(['categories' => $categories]), 'GET');
    } else {
      Utils::sendResponse(404, "Aucune catégorie trouvée", 'GET');
    }
  } else {
    Utils::sendResponse(405, "La méthode n'est pas autorisée", 'GET');
  }

} catch (Throwable $t) {
  Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), 'GET');

} catch (Exception $e) {
  Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), 'GET');
}