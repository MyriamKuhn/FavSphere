<?php

namespace Controller;

use Tools\Utils;
use Models\Category;
use config\Database;
use Exception;
use Throwable;

class CategoryController extends MainController
{
  private Category $category;
  private string $method;

  /**
   * Constructeur of the class CategoryController
   * 
   * @return void
   * @throws Exception if an error occurs
   * @throws Throwable if an error occurs
   */
  public function __construct()
  {
    try {
      parent::__construct();
      // Connexion à la base de données
      $db = Database::getInstance()->getConnection();
      // Instanciation de la classe Category
      $this->category = new Category($db);
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $_SERVER['REQUEST_METHOD']);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $_SERVER['REQUEST_METHOD']);
    }
  }

  /**
   * Handle the request
   * 
   * This method will handle the request and call the appropriate method
   * 
   * @param string $uri
   * @return void
   */
  public function handleRequest(string $uri): void
  {
    $this->method = $_SERVER['REQUEST_METHOD'];

    switch ($this->method) {
      case 'GET':
        $this->getCategories();
        break;
      case 'POST':
        $this->addCategory();
        break;
      case 'PUT':
        $this->updateCategory();
        break;
      case 'DELETE':
        $this->deleteCategory();
        break;
      default:
        Utils::sendResponse(405, "Méthode non autorisée", $this->method);
        break;
    }
  }

  /**
   * Get all categories
   * 
   * This method will get all categories from the database
   * 
   * @return void
   */
  private function getCategories()
  {
    try {
      //On récupère les catégories
      $categories = $this->category->getCategories();
      
      // Vérification s'il y a au moins une catégorie
      if ($categories) {
        Utils::sendResponse(200, ['categories' => $categories], $this->method);
      } else {
        Utils::sendResponse(404, "Aucune catégorie trouvée", $this->method);
      }
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $this->method);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $this->method);
    }
  }

  /**
   * Add a category
   * 
   * This method will add a category to the database
   * 
   * @return void
   */
  private function addCategory()
  {
    try {
      // On obtient les données envoyées en JSON
      $data = json_decode(file_get_contents("php://input"));

      // Sécurisation des données reçues
      $name = Utils::secureData($data->name);
      $color = Utils::secureData($data->color);

      // On vérifie que les données nécessaires et valides sont présentes
      if (Utils::isEmpty([$name, $color]) || Utils::checkLength($name, 3, 100) || Utils::checkHexColor($color)) {
        Utils::sendResponse(400, "Données incorrectes ou manquantes.", $this->method);
        exit;
      }

      // On hydrate l'objet
      $this->category->name = $name;
      $this->category->color = $color;

      // On tente d'ajouter la catégorie à la base de données
      if ($this->category->addCategory()) {
        Utils::sendResponse(201, "Catégorie ajoutée avec succès", $this->method);
      } else {
        Utils::sendResponse(500, "Erreur interne du serveur. Impossible d'ajouter la catégorie", $this->method);
      }
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $this->method);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $this->method);
    }
  }

  /**
   * Update a category
   * 
   * This method will update a category in the database
   * 
   * @return void
   */
  private function updateCategory()
  {
    try {
      // On obtient les données envoyées en JSON
      $data = json_decode(file_get_contents("php://input"));

      $id = Utils::cleanInt($data->id);
      $name = Utils::secureData($data->name);
      $color = Utils::secureData($data->color);

      // On vérifie que les données nécessaires et valides sont présentes
      if (Utils::isEmpty([$id, $name, $color]) || Utils::checkLength($name, 3, 100) || Utils::checkHexColor($color) || Utils::secureInt($id)) {
        Utils::sendResponse(400, "Données incorrectes ou manquantes.", $this->method);
        exit;
      }

      // On vérifie si la catégorie existe
      if ($this->category->categoryExists($id)) {

        // On hydrate l'objet
        $this->category->id = $id;
        $this->category->name = $name;
        $this->category->color = $color;

        // On tente de mettre à jour la catégorie dans la base de données
        if ($this->category->updateCategory()) {
          Utils::sendResponse(200, "Catégorie modifiée avec succès", $this->method);
        } else {
          Utils::sendResponse(500, "Erreur interne du serveur. Impossible de modifier la catégorie", $this->method);
        }
      } else {
        Utils::sendResponse(404, "La catégorie n'existe pas", $this->method);
      }
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $this->method);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $this->method);
    }
  }

  /**
   * Delete a category
   * 
   * This method will delete a category from the database
   * 
   * @return void
   */
  private function deleteCategory()
  {
    try {
      // On obtient les données envoyées en JSON
      $data = json_decode(file_get_contents("php://input"));

      // Sécurisation des données reçues
      $id = Utils::cleanInt($data->id);

      // On vérifie que les données nécessaires et valides sont présentes
      if (Utils::isEmpty([$id]) || Utils::secureInt($id)) {
        Utils::sendResponse(400, "Données incorrectes ou manquantes.", $this->method);
        exit;
      }

        // On vérifie si la catégorie existe
        if ($this->category->categoryExists($id)) {

          // On hydrate l'objet
          $this->category->id = $id;

          // On tente de supprimer la catégorie
          if ($this->category->deleteCategory()) {
            Utils::sendResponse(200, "La catégorie a été supprimée avec succès.", $this->method);
          } else {
            Utils::sendResponse(503, "Impossible de supprimer la catégorie.", $this->method);
          }
        } else {
          Utils::sendResponse(404, "La catégorie n'existe pas", $this->method);
          exit;
        }
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $this->method);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $this->method);
    }
  }
}