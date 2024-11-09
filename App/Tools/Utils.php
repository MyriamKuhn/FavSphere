<?php

namespace Tools;

class Utils
{
  /*
  * Method used to set headers for the response
  *
  * @param string $method - method HTTP used
  */
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

  /**
   * Method used to send a JSON response
   *
   * @param integer $statusCode - HTTP status code
   * @param string|array $message - message to send
   * @param string $method - HTTP method used
   * @return void
   */
  public static function sendResponse(int $statusCode, string|array $message, string $method): void
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

  /**
   * Method used to check if a string is empty
   *
   * @param array $data - data to check
   * @return bool
   */
  public static function isEmpty(array $data): bool
  {
    foreach ($data as $value) {
      if (empty($value)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Method used to clean strings (remove spaces, slashes and convert special characters to HTML entities)
   *
   * @param string $data - data to clean
   * @return string - cleaned data
   */
  public static function secureData(string $data): string
  {
    return htmlspecialchars(trim($data));
  }

  /**
   * Method used to clean integers 
   *
   * @param int $data - data to clean
   * @return int - cleaned data
   */
  public static function cleanInt(int $data): int
  {
    return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
  }

  /**
   * Method used to check if it's an integer
   *
   * @param integer $data
   * @return integer
   */
  public static function secureInt(int $data): int
  {
    return !filter_var($data, FILTER_VALIDATE_INT);
  }

  /**
   * Method used to check the length of a string
   * 
   * Max can be null if there is no maximum length to check
   *
   * @param string $data - data to check
   * @param integer $min - minimum length
   * @param integer|null $max - maximum length
   * @return bool
   */
  public static function checkLength(string $data, int $min, ?int $max): bool
  {
    if (is_null($max)) {
      return (strlen($data) < $min);
    } else {
      return (strlen($data) < $min || strlen($data) > $max);
    }
  }

  /**
   * Method used to check a hexadecimal color format
   * 
   * It's checked with a regular expression to match the format #000000
   * If the color is not valid, the method returns true
   *
   * @param string $color - color to check
   * @return bool
   */
  public static function checkHexColor(string $color): bool
  {
    return !preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
  }
  
  /**
   * Method used to check an url format
   * 
   * If the url is not valid, the method returns true
   *
   * @param string $url - url to check
   * @return bool
   */
  public static function checkUrl(string $url): bool
  {
    return !filter_var($url, FILTER_VALIDATE_URL);
  }

  /**
   * Method used to check a password format
   * 
   * It's checked with a regular expression to match the format 15 characters with at least one uppercase, one lowercase, one number and one special character
   * 
   * If the password is not valid, the method returns true
   *
   * @param string $password - password to check
   * @return bool
   */
  public static function checkPassword(string $password): bool
  {
    return !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{15,}$/', $password);
  }

}