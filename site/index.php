<!DOCTYPE html>
<html lang="fr-FR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FavSphere - La gestion facile de vos liens favoris</title>
  <link rel="shortcut icon" href="/site/assets/img/logo_small.svg" type="image/svg+xml">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="<?= $_SESSION['csrf_token']; ?>">
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
  <!-- Datatables CSS -->
  <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.1.7/b-3.1.2/b-html5-3.1.2/r-3.0.3/datatables.min.css" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="/site/assets/css/style.css">
</head>
<body class="d-flex flex-column align-items-center">
  <!-- START: Main content -->
  <main class="container pt-5 flex-grow-1 mb-5" id="main-content">

  <!-- START: Router -->
  <?php
    // Récupérer la route demandée
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // Déterminer la page à afficher
    switch ($uri) {
      case '/':
        $page = 'home.html';
        break;
      case '/legal':
        $page = 'legal.html';
        break;
      case '/favorites':
        $page = 'favorites.html';
        break;
      case '/categories':
        $page = 'categories.html';
        break;
      default:
        http_response_code(404);
        $page = '/../errors/404.html';
        break;
    }
    // Charger le contenu de la page
    require __DIR__ . "/$page";
  ?>
  <!-- END: Router -->

  </main>
  <!-- END: Main content -->

  <!-- START: Footer -->
  <footer class="py-3 mt-auto text-center mt-5">
    <p class="m-0">© 2024 FavSphere - All rights reserved</p>
    <p class="m-0">Ce site est destiné à un usage privé.</p>
    <p>Pour toute question, contactez : <a href="mailto:contact@myriamkuhn.com" class="text-uppercase fw-medium">contact@myriamkuhn.com</a></p>
    <p><a href="/legal" class="text-uppercase fw-medium link">Mentions légales</a></p>
  </footer>
  <!-- END: Footer -->

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.1.7/b-3.1.2/b-html5-3.1.2/r-3.0.3/datatables.min.js"></script>
  <!-- START: Chargement du script en fonction de la page -->
  <?php
    // Récupérer la route demandée
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    // Déterminer la page à afficher
    switch ($uri) {
      case '/':
        $page = '<script type="module" src="/site/assets/js/home.js"></script>';
        break;
      case '/favorites':
        $page = '<script type="module" src="/site/assets/js/favorites.js"></script>';
        break;
      case '/categories':
        $page = '<script type="module" src="/site/assets/js/categories.js"></script>';
        break;
    }
    echo $page;
  ?>
  <!-- END: Chargement du script en fonction de la page -->
</body>
</html>