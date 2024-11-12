<?php

namespace Models;

use Exception;
use PDO;
use PDOException;

class Link
{
  private PDO $connexion;
  private string $table = 'link';

  public int $id;
  public string $url;
  public string $title;
  public string $description;
  public int $fk_category_id;
  public int $fk_user_id;

  /**
   * Links constructor - create a database connection
   * 
   * @param PDO $db
   */
  public function __construct(PDO $db)
  {
    $this->connexion = $db;
  }

  /**
   * Get all links from one user
   * 
   * Fetch all links from the database for one user
   * 
   * @return array - return an array of all links from the database for one user, each including the category name
   * @throws Exception - throw an exception if an error occurs while fetching links for one user
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $linkModel = new Link($db);
   * $linkModel->fk_user_id = 123;
   * $links = $linkModel->getLinks();
   * print_r($links);
   * ```
   * 
   * Example of expected result:
   * ```php
   * Array
   * (
   *     [0] => Array
   *         (
   *             [id] => 1
   *             [url] => "http://example.com"
   *             [title] => "Example Link"
   *             [description] => "This is an example link"
   *             [category_name] => "Technology"
   *             [category_color] => "#FF5733"
   *         )
   *     [1] => Array
   *         (
   *             [id] => 2
   *             [url] => "http://anotherlink.com"
   *             [title] => "Another Link"
   *             [description] => "This is another link"
   *             [category_name] => "Science"
   *             [category_color] => "#33FF57"
   *         )
   * )
   * ```
   */
  public function getLinks(): array
  {
    try {
      if (empty($this->fk_user_id)) {
        throw new Exception("L'identifiant de l'utilisateur est requis");
      }

      $sql = "SELECT link.*, category.name AS category_name, category.color AS category_color
              FROM " . $this->table . " AS link
              INNER JOIN category ON link.fk_category_id = category.id
              WHERE link.fk_user_id = :fk_user_id";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':fk_user_id', $this->fk_user_id, PDO::PARAM_INT);
      // Exécution de la requête
      $stmt->execute();

      // Retourner tous les liens
      return $stmt->fetchAll();

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la récupération des liens : " . $e->getMessage());
    }
  }

  /**
   * Create a link
   * 
   * Create a new link in the database using the url, title, description, category id and user id properties of the link object
   * 
   * @return bool - return true if the link is created successfully, false otherwise
   * @throws Exception - throw an exception if an error occurs while creating a link
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $linkModel = new Link($db);
   * $linkModel->url = "https://example.com";
   * $linkModel->title = "Example Link";
   * $linkModel->description = "This is an example link.";
   * $linkModel->fk_category_id = 1;
   * $linkModel->fk_user_id = 123;
   * $linkModel->createLink();
   * ```
   */
  public function addLink(): bool
  {
    try {
      // Vérification que tous les champs requis sont renseignés
      if (empty($this->url)) {
        throw new Exception("L'URL est obligatoire");
      }
      if (empty($this->title)) {
        throw new Exception("Le titre est obligatoire");
      }
      if (empty($this->description)) {
        throw new Exception("La description est obligatoire");
      }
      if (empty($this->fk_category_id)) {
        throw new Exception("L'ID de la catégorie est obligatoire");
      }
      if (empty($this->fk_user_id)) {
        throw new Exception("L'ID de l'utilisateur est obligatoire");
      }

      // Requête pour insérer un lien dans la base de données
      $sql = "INSERT INTO " . $this->table . " (url, title, description, fk_category_id, fk_user_id) VALUE (:url, :title, :description, :fk_category_id, :fk_user_id)";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':url', $this->url, PDO::PARAM_STR);
      $stmt->bindParam(':title', $this->title, PDO::PARAM_STR);
      $stmt->bindParam(':description', $this->description, PDO::PARAM_STR);
      $stmt->bindParam(':fk_category_id', $this->fk_category_id, PDO::PARAM_INT);
      $stmt->bindParam(':fk_user_id', $this->fk_user_id, PDO::PARAM_INT);
      // Exécution de la requête
      $stmt->execute();

      // Retourner vrai si la catégorie est créée avec succès
      return $this->connexion->lastInsertId() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la création du lien : " . $e->getMessage());
    }
  }

  /**
   * Update a link
   * 
   * Update a link in the database using the id, url, title, description, category id and user id properties of the link object
   * 
   * @return bool - return true if the link is updated successfully, false otherwise
   * @throws Exception - throw an exception if an error occurs while updating a link
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $linkModel = new Link($db);
   * $linkModel->id = 1;
   * $linkModel->url = "https://example.com";
   * $linkModel->title = "Example Link";
   * $linkModel->description = "This is an example link.";
   * $linkModel->fk_category_id = 1;
   * $linkModel->fk_user_id = 123;
   * $linkModel->updateLink();
   * ```
   */
  public function updateLink(): bool
  {
    try {
      // Vérification que tous les champs requis sont renseignés
      if (empty($this->id)) {
        throw new Exception("L'ID du lien est obligatoire");
      }
      if (empty($this->url)) {
        throw new Exception("L'URL est obligatoire");
      }
      if (empty($this->title)) {
        throw new Exception("Le titre est obligatoire");
      }
      if (empty($this->description)) {
        throw new Exception("La description est obligatoire");
      }
      if (empty($this->fk_category_id)) {
        throw new Exception("L'ID de la catégorie est obligatoire");
      }
      if (empty($this->fk_user_id)) {
        throw new Exception("L'ID de l'utilisateur est obligatoire");
      }

      // Requête pour mettre à jour un lien dans la base de données
      $sql = "UPDATE " . $this->table . " SET url = :url, title = :title, description = :description, fk_category_id = :fk_category_id, fk_user_id = :fk_user_id WHERE id = :id";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':url', $this->url, PDO::PARAM_STR);
      $stmt->bindParam(':title', $this->title, PDO::PARAM_STR);
      $stmt->bindParam(':description', $this->description, PDO::PARAM_STR);
      $stmt->bindParam(':fk_category_id', $this->fk_category_id, PDO::PARAM_INT);
      $stmt->bindParam(':fk_user_id', $this->fk_user_id, PDO::PARAM_INT);
      $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
      // Exécution de la requête
      $stmt->execute();

      // Retourner vrai si le lien est mis à jour avec succès
      return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la mise à jour du lien : " . $e->getMessage());
    }
  }

  /**
   * Delete a link
   * 
   * Delete a link from the database using the id property of the link object
   * 
   * @return bool - return true if the link is deleted successfully, false otherwise
   * @throws Exception - throw an exception if an error occurs while deleting a link
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $linkModel = new Link($db);
   * $linkModel->id = 1;
   * $linkModel->deleteLink();
   * ```
   */
  public function deleteLink(): bool
  {
    try {
      // Vérification que l'ID du lien est renseigné
      if (empty($this->id)) {
        throw new Exception("L'ID du lien est obligatoire");
      }

      // Requête pour supprimer un lien
      $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
      // Exécution de la requête
      $stmt->execute();

      // Retourner vrai si le lien est supprimé avec succès
      return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la suppression du lien : " . $e->getMessage());
    }
  }

  /**
   * Check if a link exists
   * 
   * Check if a link exists in the database using the id property of the link object
   * 
   * @param int $id - the id of the link to check
   * @return bool - return true if the link exists, false otherwise
   * @throws Exception - throw an exception if an error occurs while checking if a link exists
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $linkModel = new Link($db);
   * $linkModel->id = 1;
   * $linkModel->linkExists();
   * ```
   */
  public function linkExists(int $id): bool
  {
    try {
      // Vérification que l'ID du lien est renseigné
      if (empty($id)) {
        throw new Exception("L'ID du lien est obligatoire");
      }

      // Requête pour vérifier si un lien existe
      $sql = "SELECT id FROM " . $this->table . " WHERE id = :id";
      $stmt = $this->connexion->prepare($sql);
      // Bind des paramètres
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      // Exécution de la requête
      $stmt->execute();

      // Retourner vrai si le lien existe
      return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la vérification de l'existence du lien : " . $e->getMessage());
    }
  }
}