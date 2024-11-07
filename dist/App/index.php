<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
  case '/app/':
    require_once __DIR__ . '/swagger/index.html';
    break;
  case '/app/categories':
    require_once __DIR__ . '/Controller/getCategories.php';
    break;
  case '/app/addCategory':
    require_once __DIR__ . '/Controller/addCategory.php';
    break;
  case '/app/updateCategory':
    require_once __DIR__ . '/Controller/updateCategory.php';
    break;
  case '/app/deleteCategory':
    require_once __DIR__ . '/Controller/deleteCategory.php';
    break;
  case '/app/links':
    require_once __DIR__ . '/Controller/getLinks.php';
    break;
  case '/app/addLink':
    require_once __DIR__ . '/Controller/addLink.php';
    break;
  case '/app/updateLink':
    require_once __DIR__ . '/Controller/updateLink.php';
    break;
  case '/app/deleteLink':
    require_once __DIR__ . '/Controller/deleteLink.php';
    break;
  case '/app/key':
    require_once __DIR__ . '/Controller/getKey.php';
    break;
  default:
    http_response_code(404);
    require __DIR__ . '/dist/404.php';
    break;
}