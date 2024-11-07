<?php
// On détermine le chemin actuel de la requête
$requestUri = $_SERVER['REQUEST_URI'];

// Si la requête commence par /app, c'est une requête API
if (strpos($requestUri, '/app') === 0) {
  // Inclure ici les fichiers nécessaires à ton API
  require_once 'App/index.php'; // Le fichier qui gère les routes de l'API
} else {
  // Si ce n'est pas une requête API, c'est une requête pour ton site web
  require_once 'site/index.php'; // Le fichier qui gère les pages du site
}