<?php

namespace App\config;

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use PDO;
use PDOException;

class Database{

  public function __construct()
  {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
  }

  // Connexion à la base de données
  public $connexion;

  // getter pour la connexion
  public function getConnection(){

    $this->connexion = null;

    try{
      $this->connexion = new PDO("mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
      $this->connexion->exec("set names utf8");
    }catch(PDOException $exception){
      echo "Erreur de connexion : " . $exception->getMessage();
    }

    return $this->connexion;  
  }   
}