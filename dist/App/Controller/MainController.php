<?php

namespace Controller;

use Middleware\JwtMiddleware;
use Tools\Utils;


class MainController
{
  protected JwtMiddleware $jwtMiddleware;
  protected object $decoded;

  public function __construct() {
    try {
      $this->jwtMiddleware = new JwtMiddleware();
      $this->decoded = $this->jwtMiddleware->checkJWT();
    } catch (\Exception $e) {
      Utils::sendResponse(401, $e->getMessage(), $_SERVER['REQUEST_METHOD']);
      die();
    }
  }
}