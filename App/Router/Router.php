<?php

namespace Router;

class Router
{
  private $routes = [
    '/app/' => 'swagger',
    '/app/categories' => 'CategoryController',
    '/app/addCategory' => 'CategoryController',
    '/app/updateCategory' => 'CategoryController',
    '/app/deleteCategory' => 'CategoryController',
    '/app/links' => 'LinkController',
    '/app/addLink' => 'LinkController',
    '/app/updateLink' => 'LinkController',
    '/app/deleteLink' => 'LinkController',
    '/app/login' => 'UserController',
  ];

  /**
   * Method to route the request to the right controller
   *
   * @return void
   */
  public function route()
  {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (array_key_exists($uri, $this->routes)) {
      $controller = $this->routes[$uri];

      if ($controller === 'swagger') {
        require_once __DIR__ . '/swagger/index.html';
        return;
      } else {
        $controllerClass = '\\Controller\\' . $controller;
        $controllerInstance = new $controllerClass();
        $controllerInstance->handleRequest($uri);
      }
    } else {
      http_response_code(404);
      require __DIR__ . '/../../errors/404_app.html';
      return;
    }
  }
}