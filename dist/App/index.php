<?php

// Chargement de l'autoloader
require_once 'Autoload.php';
Autoload::register();

// Chargement du routeur
$router = new Router\Router();
$router->route();