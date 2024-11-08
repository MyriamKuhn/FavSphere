<?php

namespace Controller;

use Tools\Utils;
use Models\User;
use config\Database;
use Exception;
use Middleware\JwtMiddleware;
use PDO;
use Throwable;

class UserController
{
  private User $user;
  private string $method;
  private JwtMiddleware $jwtMiddleware;

  /**
   * Constructor of the class
   * 
   * @return void
   * @throws Exception if an error occurs
   * @throws Throwable if an error occurs
   */
  public function __construct()
  {
    try {
      // Instanciation de la classe JwtMiddleware
      $this->jwtMiddleware = new JwtMiddleware();
      // Connexion à la base de données
      $db = Database::getInstance()->getConnection();
      // Instanciation de la classe Link
      $this->user = new User($db);
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $_SERVER['REQUEST_METHOD']);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $_SERVER['REQUEST_METHOD']);
    }
  }

  /**
   * Method to handle the request
   * 
   * @param string $uri
   * @return void
   */
  public function handleRequest(string $uri): void
  {
    $this->method = $_SERVER['REQUEST_METHOD'];

    switch ($this->method) {
      case 'POST':
        $this->login();
        break;
      default:
        Utils::sendResponse(405, "Méthode non autorisée", $this->method);
        break;
    }
  }

  /**
   * Method to log in a user
   * 
   * @return void
   */
  private function login(): void
  {
    try {
      // Récupération des données envoyées
      $data = json_decode(file_get_contents("php://input"));

      // Sécurisation des données
      $username = Utils::secureData($data->username);
      $password = Utils::secureData($data->password);
      //$password = $data->password;

      // Vérification des champs requis
      if (Utils::isEmpty([$username, $password]) || Utils::checkPassword($password)) {
        Utils::sendResponse(400, "Données incorrectes ou manquantes.", $this->method);
        exit;
      }
      
      // On hydrate l'objet
      $this->user->username = $username;
      $this->user->password = $password;

      // On vérifie si l'utilisateur existe
      $userId = $this->user->login();

      if ($userId !== false) {
        // Sécurisation de l'ID
        $userId = Utils::cleanInt($userId);
        // On génère un token
        $jwtToken = $this->jwtMiddleware->generateToken($userId, $username);
        // On envoie la réponse
        Utils::sendResponse(200, ["token" => $jwtToken], $this->method);
      } else {
        Utils::sendResponse(401, "Nom d'utilisateur ou mot de passe incorrect.", $this->method);
      }
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $this->method);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $this->method);
    }
  }
}