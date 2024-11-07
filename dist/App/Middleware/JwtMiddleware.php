<?php

namespace Middleware;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use Dotenv\Dotenv;


class JwtMiddleware
{
  private string $secretKey;

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
    $payload = [
      "iss" => $_SERVER['SERVER_NAME'],  // Issuer (émetteur) du token
      'aud' => $_SERVER['SERVER_NAME'],  // Audience (destinataire) du token
      "iat" => time(),  // Issued at time (date de création du token)
      "exp" => time() + 3600, // Token expiration time (1 hour)
      "userId" => $userId,  // User ID
      "username" => $username  // Username
    ];

    return JWT::encode($payload, $this->secretKey, 'HS256');
  }

  /**
   * Verify a JWT token
   * 
   * This function validates a JWT token.
   * 
   * @param string $token - the JWT token
   * @return object - return the decoded token
   * 
   * @example
   * ```php
   * $jwtMiddleware = new JwtMiddleware();
   * $token = $jwtMiddleware->generateToken(123, "john_doe");
   * $decodedToken = $jwtMiddleware->validateToken($token);
   * print_r($decodedToken);
   * ```
   */
  public function verifyToken(string $token): object
  {
    try {
      $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
      return $decoded;
    } catch (Exception $e) {
      throw new Exception("Token invalide : " . $e->getMessage());
    }
  }

  /**
   * Get user information from a JWT token
   * 
   * This function extracts the user ID and username from a JWT token.
   * 
   * @param string $token - the JWT token
   * @return array - returns an array with userId and username
   * @throws Exception - throw an exception if the token is invalid
   * 
   * @example
   * ```php
   * $jwtMiddleware = new JwtMiddleware();
   * $token = $jwtMiddleware->generateToken(123, "john_doe");
   * $user = $jwtMiddleware->getUserInfo($token);
   * echo $user['userId'];  // 123
   * echo $user['username'];  // john_doe
   * ```
   */
  public function getUserInfo(string $token): array
  {
    try {
      $decoded = $this->verifyToken($token);
      return [
        'userId' => $decoded->userId,
        'username' => $decoded->username
      ];
    } catch (Exception $e) {
      throw new Exception("Impossible de récupérer les informations de l'utilisateur : " . $e->getMessage());
    }
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
        return $decoded; // Retourne les données du token si validé
      } catch (Exception $e) {
        // Si le token est invalide, lancer une exception
        throw new Exception("Accès refusé. Token invalide : " . $e->getMessage());
      }
    } else {
      throw new Exception("Accès refusé. Token manquant.");
    }
  }
}