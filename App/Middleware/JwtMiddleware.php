<?php

namespace Middleware;

require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Exception;
use Dotenv\Dotenv;
use config\Database;
use Models\User;


class JwtMiddleware
{
  private string $secretKey;

  /**
   * JwtMiddleware constructor.
   * 
   * This constructor loads the environment variables and checks if the JWT secret key is defined.
   * 
   * @throws Exception - throw an exception if the JWT secret key is missing or empty
   * 
   * @example
   * ```php
   * $jwtMiddleware = new JwtMiddleware();
   * ```
   */
  public function __construct()
  {
    // Charger les variables d'environnement
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    // Vérifier si la clé secrète JWT est définie dans le fichier .env
    if (!isset($_ENV['JWT_SECRET']) || empty($_ENV['JWT_SECRET'])) {
      throw new Exception("La clé secrète JWT est manquante ou vide dans le fichier .env");
    }

    // Assigner la clé secrète à la propriété
    $this->secretKey = $_ENV['JWT_SECRET'];
  }

  /**
   * Generate a JWT token
   * 
   * This function generates a JWT token with the user ID and username.
   * 
   * @param int $userId - the user ID
   * @param string $username - the username
   * @return string - return the generated JWT token
   * 
   * @example
   * ```php
   * $jwtMiddleware = new JwtMiddleware();
   * $token = $jwtMiddleware->generateToken(123, "john_doe");
   * echo $token;
   * ```
   */
  public function generateToken(int $userId, string $username): string
  {
    // Vérifier que l'ID est un entier valide
    if (!filter_var($userId, FILTER_VALIDATE_INT)) {
      throw new Exception("ID utilisateur invalide");
    }
    // Nettoyage des données
    $safeUsername = trim(htmlentities($username, ENT_QUOTES, 'UTF-8'));

    // Créer le payload du token
    $payload = [
      "iss" => $_SERVER['SERVER_NAME'],  // Issuer (émetteur) du token
      'aud' => $_SERVER['SERVER_NAME'],  // Audience (destinataire) du token
      "iat" => time(),  // Issued at time (date de création du token)
      "exp" => time() + 3600, // Token expiration time (1 hour)
      "userId" => $userId,  // User ID
      "username" => $safeUsername  // Username
    ];

    return JWT::encode($payload, $this->secretKey, 'HS256');
  }

  /**
   * Middleware to check if a user is authenticated
   * 
   * This function checks if a user is authenticated by verifying the JWT token.
   *
   * If the token is valid, the user information is added to the request object.
   * 
   * @return object - return the request object with user information
   * @throws Exception - throw an exception if the token is invalid
   * 
   * @example
   * ```php
   * $jwtMiddleware = new JwtMiddleware();
   * $decoded = $jwtMiddleware->checkJWT();
   * echo $decoded->userId;  // 123
   * echo $decoded->username;  // john_doe
   * ```
   */
  public function checkJWT(): object
  {
    // Vérifie si le token est présent dans les headers Authorization
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
      $token = str_replace("Bearer ", "", $authHeader);

      try {
        // Décoder le token
        $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

        // Vérifier si l'utilisateur existe dans la base de données
        $db = Database::getInstance()->getConnection();
        $user = new User($db);

        // Assigner les valeurs de l'utilisateur depuis le token
        $user->id = $decoded->userId;
        $user->username = $decoded->username;

        // Vérifier si l'utilisateur existe
        if (!$user->userExists()) {
          throw new Exception("Utilisateur introuvable.");
        }

        // Ajout des informations utilisateur au token décodé
        $decoded->user = [
          'id' => $user->id,
          'username' => $user->username,
        ];

        return $decoded; // Retourne les données du token si validé

      } catch (ExpiredException $e) {
        throw new Exception("Accès refusé. Token expiré : " . $e->getMessage());

      } catch (Exception $e) {
        // Si le token est invalide, lancer une exception
        throw new Exception("Accès refusé. Token invalide : " . $e->getMessage());
      }
    } else {
      throw new Exception("Accès refusé. Token manquant.");
    }
  }
}