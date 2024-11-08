<?php

use Middleware\JwtMiddleware;

$jwtMiddleware = new JwtMiddleware();
echo $jwtMiddleware->generateToken(1, "Myriam");