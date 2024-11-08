<?php

namespace Controller;

use Tools\Utils;
use Models\Link;
use Models\Category;
use config\Database;
use Exception;
use PDO;
use Throwable;

class LinkController extends MainController
{
  private PDO $db;
  private Link $link;
  private string $method;
  private int $userId;

  /**
   * Constructor of the class LinkController
   * 
   * @return void
   * @throws Exception 
   * @throws Throwable 
   */
  public function __construct()
  {
    try {
      parent::__construct();
      // Connexion à la base de données
      $this->db = Database::getInstance()->getConnection();
      // Instanciation de la classe Link
      $this->link = new Link($this->db);
      // On récupère l'ID du user dans le token
      $this->userId = $this->decoded->user['id'];
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
      case 'GET':
        $this->getLinks();
        break;
      case 'POST':
        $this->addLink();
        break;
      case 'PUT':
        $this->updateLink();
        break;
      case 'DELETE':
        $this->deleteLink();
        break;
      default:
        Utils::sendResponse(405, "Méthode non autorisée", $this->method);
        break;
    }
  }

  /**
   * Method to get all links
   * 
   * @return void
   */
  private function getLinks()
  {
    try {
      // On sécurise l'ID du user
      $userId = Utils::cleanInt($this->userId);

      // On vérifie si l'ID est un entier et pas vide
      if (Utils::isEmpty([$userId]) || Utils::secureInt($userId)) {
        Utils::sendResponse(400, "Données incorrectes ou manquantes.", $this->method);
        exit;
      }

      // On hydrate l'objet Link
      $this->link->fk_user_id = $userId;

      //On récupère les liens
      $links = $this->link->getLinks();
    
      // Vérification s'il y a au moins un lien
      if ($links) {
        Utils::sendResponse(200, ['links' => $links], $this->method);
      } else {
        Utils::sendResponse(404, "Aucun lien trouvé", $this->method);
      }
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $this->method);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $this->method);
    }
  }

  /**
   * Method to add a link
   * 
   * @return void
   */
  private function addLink()
  {
    try {
      // On obtient les données envoyées en JSON
      $data = json_decode(file_get_contents("php://input"));

      // On sécurise les données
      $url = Utils::secureData($data->url);
      $title = Utils::secureData($data->title);
      $description = Utils::secureData($data->description);
      $fk_category_id = Utils::cleanInt($data->fk_category_id);
      $fk_user_id = Utils::cleanInt($this->userId);

      // On vérifie si les données sont vides ou incorrectes et on les valide
      if (Utils::isEmpty([$url, $title, $description, $fk_category_id, $fk_user_id]) || Utils::checkUrl($url) || Utils::checkLength($title, 3, 100) || Utils::checkLength($description, 3, null) || Utils::secureInt($fk_category_id) || Utils::secureInt($fk_user_id)) {
        Utils::sendResponse(400, "Données incorrectes ou manquantes.", $this->method);
        exit;
      }

      // On vérifie si la catégorie existe
      $category = new Category($this->db);
      if (!$category->categoryExists($fk_category_id)) {
        Utils::sendResponse(400, "Catégorie inexistante", $this->method);
        exit;
      }

      // On hydrate l'objet
      $this->link->url = $url;
      $this->link->title = $title;
      $this->link->description = $description;
      $this->link->fk_category_id = $fk_category_id;
      $this->link->fk_user_id = $fk_user_id;

      // On tente d'ajouter le lien à la base de données
      if ($this->link->addLink()) {
        Utils::sendResponse(201, "Lien ajouté avec succès", $this->method);
      } else {
        Utils::sendResponse(500, "Erreur interne du serveur. Impossible d'ajouter le lien.", $this->method);
      }
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $this->method);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $this->method);
    }
  }

  /**
   * Method to update a link
   * 
   * @return void
   */
  private function updateLink()
  {
    try {
      // On obtient les données envoyées en JSON
      $data = json_decode(file_get_contents("php://input"));

      // On sécurise les données
      $id = Utils::cleanInt($data->id);
      $url = Utils::secureData($data->url);
      $title = Utils::secureData($data->title);
      $description = Utils::secureData($data->description);
      $fk_category_id = Utils::cleanInt($data->fk_category_id);
      $fk_user_id = Utils::cleanInt($this->userId);

      // On vérifie si les données sont vides ou incorrectes et on les valide
      if (Utils::isEmpty([$id, $url, $title, $description, $fk_category_id, $fk_user_id]) || Utils::secureInt($id) || Utils::checkUrl($url) || Utils::checkLength($title, 3, 100) || Utils::checkLength($description, 3, null) || Utils::secureInt($fk_category_id) || Utils::secureInt($fk_user_id)) {
        Utils::sendResponse(400, "Données incorrectes ou manquantes.", $this->method);
        exit;
      }

      // On vérifie si la catégorie existe
      $category = new Category($this->db);
      if (!$category->categoryExists($fk_category_id)) {
        Utils::sendResponse(400, "Catégorie inexistante", $this->method);
        exit;
      }

      // On vérifie si le lien existe
      if ($this->link->linkExists($id)) {

        // On hydrate l'objet
        $this->link->id = $id;
        $this->link->url = $url;
        $this->link->title = $title;
        $this->link->description = $description;
        $this->link->fk_category_id = $fk_category_id;
        $this->link->fk_user_id = $fk_user_id;

        // On tente de mettre à jour le lien dans la base de données
        if ($this->link->updateLink()) {
          Utils::sendResponse(200, "Lien modifié avec succès", $this->method);
        } else {
          Utils::sendResponse(500, "Erreur interne du serveur. Impossible de modifier le lien.", $this->method);
        }
      } else {
        Utils::sendResponse(404, "Le lien n'existe pas", $this->method);
      }
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $this->method);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $this->method);
    }
  }

  /**
   * Method to delete a link
   * 
   * @return void
   */
  private function deleteLink()
  {
    try {
      // On obtient les données envoyées en JSON
      $data = json_decode(file_get_contents("php://input"));

      // On sécurise les données
      $id = Utils::cleanInt($data->id);

      // On vérifie si les données sont vides ou incorrectes et on les valide
      if (Utils::isEmpty([$id]) || Utils::secureInt($id)) {
        Utils::sendResponse(400, "Données incorrectes ou manquantes.", $this->method);
        exit;
      }

      // On vérifie si le lien existe
      if ($this->link->linkExists($id)) {
        // On hydrate l'objet
        $this->link->id = $id;

        // On tente de supprimer le lien
        if ($this->link->deleteLink()) {
          Utils::sendResponse(200, "Le lien a été supprimé avec succès", $this->method);
        } else {
          Utils::sendResponse(503, "Impossible de supprimer le lien.", $this->method);
        }
      } else {
        Utils::sendResponse(404, "Le lien n'existe pas", $this->method);
      }
    } catch (Throwable $t) {
      Utils::sendResponse(500, "Erreur interne du serveur. " . $t->getMessage(), $this->method);
    } catch (Exception $e) {
      Utils::sendResponse(401, "Accès refusé. " . $e->getMessage(), $this->method);
    }
  }
}