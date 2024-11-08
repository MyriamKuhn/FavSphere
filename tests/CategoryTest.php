<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;

class CategoryTest extends TestCase
{

	// Charger les variables d'environnement dans setUp() au lieu du constructeur
	protected function setUp(): void
	{
		parent::setUp(); // N'oublie pas d'appeler le parent

		// Charger les variables d'environnement à partir du fichier .env.test
		if (file_exists(__DIR__ . '/../.env.test')) {
			$dotenv = Dotenv::createImmutable(__DIR__ . '/../', '.env.test');
			$dotenv->load();
		}
	}

	// L'URL de base de ton API (à adapter selon ton projet)
	private $baseUrl = 'http://favsphere.local/app/categories';

	// Test pour récupérer toutes les catégories
	public function testGetCategories()
	{
		// Envoie la requête et récupère la réponse avec un token valide par défaut
		$response = $this->getJson($this->baseUrl);

		// Affiche la réponse pour déboguer
		echo "Réponse : ";
		print_r($response);

		// Vérifie que la réponse a le statut 200
		$this->assertEquals(200, $response['status']);

		// Vérifie que la clé 'categories' existe bien dans la réponse
		$this->assertArrayHasKey('categories', $response['data']);

		// Vérifie que 'categories' est bien un tableau
		$this->assertIsArray($response['data']['categories']);
	}


	public function testGetCategoriesUnauthorized()
	{
		$invalidToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9';  // Token invalide ou malformé
		$response = $this->getJson($this->baseUrl, ['Authorization' => 'Bearer ' . $invalidToken]);

		// Affiche la réponse complète pour comprendre le résultat
		echo "Réponse non autorisée : ";
		print_r($response);

		// Assure-toi que la réponse contient le code 401
		$this->assertEquals(401, $response['status']);

		// Vérifie que le message d'erreur est celui attendu
		$this->assertArrayHasKey('message', $response['data']);
		$this->assertEquals('Accès refusé. Token invalide : Wrong number of segments', $response['data']['message']);
	}




	// Une méthode d'aide pour effectuer les requêtes GET
	private function getJson($url, $headers = [])
	{
		// Crée une ressource cURL
		$ch = curl_init();

		// Si des en-têtes sont passés, on les utilise, sinon on met les en-têtes par défaut
		if (empty($headers)) {
			$headers = [
				'Authorization: Bearer ' . $this->getToken()  // Token valide par défaut
			];
		}

		// Configure la requête GET
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		// Activer le mode de débogage
		curl_setopt($ch, CURLOPT_VERBOSE, true);  // Affiche des détails de la requête cURL

		// Exécuter la requête et obtenir la réponse
		$response = curl_exec($ch);

		// Récupère le code de statut HTTP
		$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Ferme la ressource cURL
		curl_close($ch);

		// Affiche la réponse brute et les informations de statut pour le débogage
		echo "Code de statut : " . $statusCode . "\n";
		echo "Réponse brute : " . $response . "\n";

		// Vérifie si la réponse est valide
		if ($response === false) {
			return ['status' => $statusCode, 'data' => null];
		}

		// Convertir la réponse JSON en tableau PHP
		$responseData = json_decode($response, true);

		// Retourner à la fois le code de statut et les données
		return [
			'status' => $statusCode,
			'data' => $responseData
		];
	}


	// Optionnel : méthode pour générer un token pour les tests
	private function getToken()
	{
		// Génère ou récupère un token d'accès pour les tests (tu peux le pré-créer manuellement si nécessaire)
		return $_ENV['TOKEN'];
	}
}
