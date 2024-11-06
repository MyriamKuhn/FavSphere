<?php

namespace App\models;

use Exception;
use PDO;
use PDOException;

class User
{
  private PDO $connexion;
  private string $table = 'user';

  public int $id;
  public string $username;
  public string $password;

  /**
   * User constructor - create a database connection
   * 
   * @param PDO $db
   */
  public function __construct(PDO $db)
  {
    $this->connexion = $db;
  }

  /**
   * Authenticate a user with a username and password
   * 
   * This function checks if the user exists in the database and if the password is correct.
   * The password is hashed in the database, so we use the password_verify function to compare 
   * the password entered by the user with the hashed password in the database.
   * 
   * If the user exists and the password is correct, the function returns true.
   * If the user does not exist or the password is incorrect, the function returns false.
   * 
   * @return bool - return true if the user is authenticated, false otherwise
   * @throws Exception - throw an exception if an error occurs while authenticating the user
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $userModel = new User($db);
   * $userModel->username = "john_doe";
   * $userModel->password = "password";
   * $isAuthenticated = $userModel->authenticate();
   * echo $isAuthenticated;
   * ```
   */
  public function authenticate(): bool
  {
    // Vérification des champs requis
    if (empty($this->username)) {
      throw new Exception("Le nom de l'utilisateur est requis");
    }
    if (empty($this->password)) {
      throw new Exception("Le mot de passe de l'utilisateur est requis");
    }

    try {
      // Requête pour vérifier si l'utilisateur existe
      $sql = "SELECT * FROM " . $this->table . " WHERE username = :username LIMIT 1";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':username', $this->username, $this->connexion::PARAM_STR);
      // Exécution de la requête
      $stmt->execute();

      // Récupération de la ligne
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      // Si l'utilisateur existe et que le mot de passe est correct
      if ($row && password_verify($this->password, $row['password'])) {
        return true; 
      }

      // Si l'utilisateur n'existe pas ou le mot de passe est incorrect
      return false;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de l'authentification : " . $e->getMessage());
    }
  }
}