<?php

namespace Models;

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
   * If the user exists and the password is correct, the function returns the userId.
   * If the user does not exist or the password is incorrect, the function returns false.
   * 
   * @return bool|int - return false if the user does not exist or the password is incorrect, the user ID otherwise
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
  public function login(): bool|int
  {
    try {
      // Vérification des champs requis
      if (empty($this->username)) {
        throw new Exception("Le nom de l'utilisateur est requis");
      }
      if (empty($this->password)) {
        throw new Exception("Le mot de passe de l'utilisateur est requis");
      }

      // Requête pour vérifier si l'utilisateur existe
      $sql = "SELECT * FROM " . $this->table . " WHERE user = :username LIMIT 1";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
      // Exécution de la requête
      $stmt->execute();

      // Récupération de la ligne
      $row = $stmt->fetch();

      // Si l'utilisateur existe et que le mot de passe est correct
      if ($row && password_verify($this->password, $row['password'])) {
        return $row['id'];
      } else {
      // Si l'utilisateur n'existe pas ou le mot de passe est incorrect
      return false;
      }

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de l'authentification : " . $e->getMessage());
    }
  }

  /**
   * Register a new user
   * 
   * This function creates a new user in the database using the username and password properties of the user object.
   * The password is hashed using the password_hash function before being stored in the database.
   * 
   * @return bool - return true if the user is created successfully, false otherwise
   * @throws Exception - throw an exception if an error occurs while creating a user
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $userModel = new User($db);
   * $userModel->username = "john_doe";
   * $userModel->password = "password";
   * $isCreated = $userModel->register();
   * echo $isCreated;
   * ```
   */
  public function register(): bool
  {
    try {
      // Vérification des champs requis
      if (empty($this->username)) {
        throw new Exception("Le nom de l'utilisateur est requis");
      }
      if (empty($this->password)) {
        throw new Exception("Le mot de passe de l'utilisateur est requis");
      }

      // Requête pour insérer un nouvel utilisateur
      $sql = "INSERT INTO " . $this->table . " (user, password) VALUE (:username, :password)";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
      $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
      $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
      // Exécution de la requête
      $stmt->execute();

      // Retourner vrai si l'utilisateur est créé avec succès
      return $this->connexion->lastInsertId() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de l'inscription : " . $e->getMessage());
    }
  }

  /**
   * Update a user
   * 
   * This function updates a user in the database using the id, username and password properties of the user object.
   * The password is hashed using the password_hash function before being stored in the database.
   * 
   * @return bool - return true if the user is updated successfully, false otherwise
   * @throws Exception - throw an exception if an error occurs while updating a user
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $userModel = new User($db);
   * $userModel->id = 1;
   * $userModel->username = "john_doe";
   * $userModel->password = "password";
   * $isUpdated = $userModel->updateUser();
   * echo $isUpdated;
   * ```
   */
  public function updateUser(): bool
  {
    try {
      // Vérification des champs requis
      if (empty($this->id)) {
        throw new Exception("L'ID de l'utilisateur est requis");
      }
      if (empty($this->username)) {
        throw new Exception("Le nom de l'utilisateur est requis");
      }
      if (empty($this->password)) {
        throw new Exception("Le mot de passe de l'utilisateur est requis");
      }

      // Requête pour mettre à jour un utilisateur
      $sql = "UPDATE " . $this->table . " SET user = :username, password = :password WHERE id = :id";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
      $stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
      $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
      $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
      // Exécution de la requête
      $stmt->execute();

      // Retourner vrai si l'utilisateur est mis à jour avec succès
      return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la mise à jour de l'utilisateur : " . $e->getMessage());
    }
  }

  /**
   * Delete a user
   * 
   * This function deletes a user in the database using the id property of the user object.
   * 
   * @return bool - return true if the user is deleted successfully, false otherwise
   * @throws Exception - throw an exception if an error occurs while deleting a user
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $userModel = new User($db);
   * $userModel->id = 1;
   * $isDeleted = $userModel->deleteUser();
   * echo $isDeleted;
   * ```
   */
  public function deleteUser(): bool
  {
    try {
      // Vérification des champs requis
      if (empty($this->id)) {
        throw new Exception("L'ID de l'utilisateur est requis");
      }

      // Requête pour supprimer un utilisateur
      $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
      // Exécution de la requête
      $stmt->execute();

      // Retourner vrai si l'utilisateur est supprimé avec succès
      return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
    }
  }

  /**
   * Check if a user exists
   * 
   * This function checks if a user exists in the database using the id property of the user object.
   * 
   * @return bool - return true if the user exists, false otherwise
   * @throws Exception - throw an exception if an error occurs while checking if a user exists
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $userModel = new User($db);
   * $userModel->id = 1;
   * $userModel->username = "john_doe";
   * $userExists = $userModel->userExists();
   * echo $userExists;
   * ```
   */
  public function userExists(): bool
  {
    try {
      // Vérification des champs requis
      if (empty($this->id)) {
        throw new Exception("L'ID de l'utilisateur est requis");
      }
      if (empty($this->username)) {
        throw new Exception("Le nom de l'utilisateur est requis");
      }

      // Requête pour vérifier si l'utilisateur existe
      $sql = "SELECT * FROM " . $this->table . " WHERE id = :id AND user = :username LIMIT 1";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
      $stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
      // Exécution de la requête
      $stmt->execute();

      // Retourner vrai si l'utilisateur existe
      return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la vérification de l'existence de l'utilisateur : " . $e->getMessage());
    }
  }
}