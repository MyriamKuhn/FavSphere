<?php

namespace config;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Dotenv\Dotenv;
use Exception;
use PDO;
use PDOException;

class Database{

  /**
   * Database constructor
   * 
   * Load the environment variables
   * 
   * @return void
   * 
   */
  public function __construct()
  {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
  }

  private ?PDO $connexion = null;
  private static ?self $instance = null;

  /**
   * Get the instance of the Database class
   * 
   * It's a singleton pattern to avoid multiple connections to the database
   * 
   * @return self
   * 
   */
  public static function getInstance(): self 
  {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Get the connection to the database
   * 
   * @return PDO
   * @throws Exception if the connection to the database fails
   */
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