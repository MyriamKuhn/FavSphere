<?php
namespace Models;

use Exception;
use PDO;
use PDOException;

class Category 
{
  private PDO $connexion;
  private string $table = 'category';

  public int $id;
  public string $name;
  public string $color;
  public int $fk_user_id;

  /**
   * Category constructor - create a database connection
   * 
   * @param PDO $db
   */
  public function __construct(PDO $db)
  {
    $this->connexion = $db;
  }

  /**
   * Get all categories for one user
   * 
   * Fetch all categories from the database for a specific user using the fk_user_id property of the category object
   * 
   * @return array - return an array of all categories from the database
   * @throws Exception - throw an exception if an error occurs while fetching categories
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $categoryModel = new Category($db);
   * $categories = $categoryModel->getCategories();
   * print_r($categories);
   * ```
   * 
   * Example of expected result:
   * ```php
   * Array
   * (
   *     [0] => Array
   *         (
   *             [id] => 1
   *             [name] => "Technology"
   *             [color] => "#FF0000"
   *             [fk_user_id] => 1
   *         )
   *     [1] => Array
   *         (
   *             [id] => 2
   *             [name] => "Science"
   *             [color] => "#FF0000"
   *             [fk_user_id] => 1
   *         )
   *     [2] => Array
   *         (
   *             [id] => 3
   *             [name] => "Arts"
   *             [color] => "#FF0000"
   *             [fk_user_id] => 1
   *         )
   * )
   * ```
   */
  public function getCategories(): array
  {
    try {
      // Requête SQL pour récupérer toutes les catégories
      $sql = "SELECT * FROM " . $this->table . " WHERE fk_user_id = :fk_user_id";
      $stmt = $this->connexion->prepare($sql);
      // Liaison des paramètres
      $stmt->bindParam(':fk_user_id', $this->fk_user_id, PDO::PARAM_INT);
      // Exécuter la requête
      $stmt->execute();

      // Récupérer toutes les catégories
      return $stmt->fetchAll();

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la récupération des catégories : " . $e->getMessage());
    }
  }

  /**
   * Create a category
   * 
   * Create a new category in the database using the name property of the category object
   * 
   * @return bool - return true if the category is created successfully, false otherwise
   * @throws Exception - throw an exception if an error occurs while creating a category
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $categoryModel = new Category($db);
   * $categoryModel->name = "Technology";
   * $categoryModel->color = "#FF0000";
   * $categoryModel->fk_user_id = 1;
   * $categoryModel->createCategory();
   * ```
   */
  public function addCategory(): bool
  {
    try {
      // Vérifier si le champs ne sont pas vides
      if (empty($this->name)) {
        throw new Exception("Le nom de la catégorie est obligatoire");
      }
      if (empty($this->color)) {
        throw new Exception("La couleur de la catégorie est obligatoire");
      }

      // Requête SQL pour insérer une nouvelle catégorie
      $sql = "INSERT INTO " . $this->table . " (name, color, fk_user_id) VALUE (:name, :color, :fk_user_id)";
      $stmt = $this->connexion->prepare($sql);
      // Lier les paramètres
      $stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
      $stmt->bindParam(':color', $this->color, PDO::PARAM_STR);
      $stmt->bindParam(':fk_user_id', $this->fk_user_id, PDO::PARAM_INT);
      // Exécuter la requête
      $stmt->execute();

      // Retourner vrai si la catégorie est créée avec succès
      return $this->connexion->lastInsertId() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la création de la catégorie : " . $e->getMessage());
    }
  }

  /**
   * Update a category
   * 
   * Update a category in the database using the id and name properties of the category object
   * 
   * @return bool - return true if the category is updated successfully, false otherwise
   * @throws Exception - throw an exception if an error occurs while updating a category
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $categoryModel = new Category($db);
   * $categoryModel->id = 1;
   * $categoryModel->name = "Technology";
   * $categoryModel->color = "#FF0000";
   * $categoryModel->fk_user_id = 1;
   * $categoryModel->updateCategory();
   * ```
   */
  public function updateCategory(): bool
  {
    try {
      // Vérifier si l'ID et le nom de la catégorie sont renseignés
      if (empty($this->id)) {
        throw new Exception("L'ID de la catégorie est obligatoire");
      }
      if (empty($this->name)) {
        throw new Exception("Le nom de la catégorie est obligatoire");
      }
      if (empty($this->color)) {
        throw new Exception("La couleur de la catégorie est obligatoire");
      }

      // Requête SQL pour mettre à jour une catégorie
      $sql = "UPDATE " . $this->table . " SET name = :name, color = :color, fk_user_id = :fk_user_id WHERE id = :id";
      $stmt = $this->connexion->prepare($sql);
      // Lier les paramètres
      $stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
      $stmt->bindParam(':color', $this->color, PDO::PARAM_STR);
      $stmt->bindParam(':fk_user_id', $this->fk_user_id, PDO::PARAM_INT);
      $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
      // Exécuter la requête
      $stmt->execute();

      // Retourner vrai si la catégorie est mise à jour avec succès
      return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la mise à jour de la catégorie : " . $e->getMessage());
    }
  }

  /**
   * Delete a category
   * 
   * Delete a category from the database using the id property of the category object
   * 
   * @return bool - return true if the category is deleted successfully, false otherwise
   * @throws Exception - throw an exception if an error occurs while deleting a category
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $categoryModel = new Category($db);
   * $categoryModel->id = 1;
   * $categoryModel->deleteCategory();
   * ```
   */
  public function deleteCategory(): bool
  {
    try {
      // Vérifier si l'ID de la catégorie est renseigné
      if (empty($this->id)) {
        throw new Exception("L'ID de la catégorie est obligatoire");
      }

      // Requête SQL pour supprimer une catégorie
      $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
      $stmt = $this->connexion->prepare($sql);
      // Lier les paramètres
      $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
      // Exécuter la requête
      $stmt->execute();

      // Retourner vrai si la catégorie est supprimée avec succès
      return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la suppression de la catégorie : " . $e->getMessage());
    }
  }

  /**
   * Check if a category exists
   * 
   * Check if a category exists in the database using the id property of the category object
   * 
   * @param int $id - the category ID
   * @return bool - return true if the category exists, false otherwise
   * 
   * @example
   * ```php
   * $db = Database::getInstance()->getConnection();
   * $categoryModel = new Category($db);
   * $categoryModel->id = 1;
   * $categoryModel->categoryExists(1);
   * ```
   */
  public function categoryExists(int $id): bool
  {
    try {
      // Requête SQL pour vérifier si la catégorie existe
      $sql = "SELECT id FROM " . $this->table . " WHERE id = :id LIMIT 1";
      $stmt = $this->connexion->prepare($sql);
      // Lier les paramètres
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      // Exécuter la requête
      $stmt->execute();

      // Retourner vrai si la catégorie existe
      return $stmt->rowCount() > 0;

    } catch (PDOException $e) {
      throw new Exception("Erreur lors de la vérification de l'existence de la catégorie : " . $e->getMessage());
    }
  }
}