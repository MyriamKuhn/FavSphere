<?php

// Chargement de l'autoloader
require_once __DIR__.'/../Autoload.php';
Autoload::register();

use Middleware\JwtMiddleware;

$jwtMiddleware = new JwtMiddleware();
echo $jwtMiddleware->generateToken(123, "john");