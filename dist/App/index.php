<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
  case '/app/':
    require_once __DIR__ . '/swagger/index.html';
    break;
  case '/app/categories':
    require_once __DIR__ . '/Controller/getCategories.php';
    break;
  case '/app/key':
    require_once __DIR__ . '/Controller/getKey.php';
    break;
  default:
    http_response_code(404);
    require __DIR__ . '/dist/404.php';
    break;
}