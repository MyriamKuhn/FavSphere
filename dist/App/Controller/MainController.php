<?php

namespace Controller;

use Middleware\JwtMiddleware;
use Tools\Utils;


class MainController
{
  protected JwtMiddleware $jwtMiddleware;
  protected object $decoded;

  /**
   * MainController constructor.
   * 
   * Verify the JWT token and decode it
   * 
   * @return void
   * @throws \Exception if the JWT token is invalid or expired and send a 401 response with the error message then die
   */
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