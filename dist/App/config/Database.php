<?php

namespace config;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Dotenv\Dotenv;
use Exception;
use PDO;
use PDOException;

class Database{

  public function __construct()
  {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
  }

  private ?PDO $connexion = null;
  private static ?self $instance = null;

  // instance de la classe Database (singleton)
  public static function getInstance(): self 
  {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  // getter pour la connexion
  public function getConnection(): PDO
  {
    if (is_null($this->connexion)) {
      try{

        $this->connexion = new PDO("mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        $this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connexion->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->connexion->exec("set names utf8");

      } catch(PDOException $exception) {

        throw new Exception("Erreur de connexion : " . $exception->getMessage());
      }
    }

    return $this->connexion;  
    
  }   
}