<?php

namespace Tools;

class Utils
{
  // Méthode pour envoyer les en-têtes HTTP
  public static function setHeaders(string $method): void
  {
    // En-têtes par défaut
    $headers = [
      "Access-Control-Allow-Origin: " . $_SERVER['SERVER_NAME'],
      "Content-Type: application/json; charset=UTF-8",
      "Access-Control-Allow-Methods: " . $method,
      "Access-Control-Max-Age: 3600",
      "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With",
    ];

    // Applique chaque en-tête
    foreach ($headers as $header) {
      header($header);
    }
  }

  // Méthode pour envoyer une réponse JSON
  public static function sendResponse(int $statusCode, string|array $message,string $method): void
  {
    // Application des headers avant d'envoyer la réponse
    self::setHeaders($method);
    // Définition du code de réponse
    http_response_code($statusCode);
    // Encodage du message en UTF-8
    if (is_array($message)) {
      $message = json_encode($message, JSON_UNESCAPED_UNICODE);
    } else {
      $message = json_encode(["message" => $message], JSON_UNESCAPED_UNICODE);
    }
    // Envoi du message en JSON
    echo $message;
  }

  // Méthode pour vérifier si une chaîne de caractères est vide
  public static function isEmpty(array $data): bool
  {
    foreach ($data as $value) {
      if (empty($value)) {
        return true;
      }
    }
    return false;
  }

  // Méthode pour nettoyer les strings
  public static function secureData(string $data): string
  {
    return htmlspecialchars(trim($data));
  }

  // Méthode pour nettoyer les int
  public static function cleanInt(int $data): int
  {
    return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
  }

  // Méthode pour sécuriser les ints
  public static function secureInt(int $data): int
  {
    return !filter_var($data, FILTER_VALIDATE_INT);
  }

  // Méthode pour contrôler la longueur d'une chaîne de caractères
  public static function checkLength(string $data, int $min, ?int $max): bool
  {
    if (is_null($max)) {
      return (strlen($data) < $min);
    } else {
      return (strlen($data) < $min || strlen($data) > $max);
    }
  }

  // Méthode pour vérifier un code hexadécimal
  public static function checkHexColor(string $color): bool
  {
    return !preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
  }
  
 // Méthode pour vérifier un format d'URL
  public static function checkUrl(string $url): bool
  {
    return !filter_var($url, FILTER_VALIDATE_URL);
  }

}