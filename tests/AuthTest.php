<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
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

	public function post($url, $data)
	{
		// Utilisation de cURL pour envoyer la requête POST
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
		]);
		$response = curl_exec($ch);
		curl_close($ch);

		return json_decode($response, true);
	}

	public function testLoginWithValidCredentials()
	{
		$data = [
			'username' => $_ENV['USER_NAME'],
			'password' => $_ENV['USER_PASSWORD'],
		];

		$response = $this->post('http://favsphere.local/app/login', $data);

		// Vérifier que la réponse contient un token JWT
		$this->assertArrayHasKey('token', $response);
		$this->assertNotEmpty($response['token']);
	}

	public function testLoginWithInvalidCredentials()
	{
		$data = [
			'username' => 'invalid_user',
			'password' => 'invalid_password',
		];

		$response = $this->post('http://favsphere.local/app/login', $data);

		// Vérifier que la réponse indique une erreur d'authentification
		$this->assertArrayHasKey('message', $response);
		$this->assertEquals('Données incorrectes ou manquantes.', $response['message']);
	}

	public function testLoginResponseTime()
	{
		$data = [
			'username' => $_ENV['USER_NAME'],
			'password' => $_ENV['USER_PASSWORD'],
		];

		$start = microtime(true); // Enregistrer l'heure avant la requête
		$response = $this->post('http://favsphere.local/app/login', $data);
		$end = microtime(true); // Enregistrer l'heure après la requête

		$executionTime = $end - $start; // Calculer la différence (temps de réponse)

		// Vérifier que le temps d'exécution est inférieur à 1 seconde
		$this->assertLessThan(1, $executionTime, 'Le temps de réponse du login dépasse 1 seconde');
	}

	public function testSqlInjection()
	{
		// Test d'injection SQL dans les champs
		$data = [
			'username' => "' OR '1'='1",
			'password' => "' OR '1'='1"
		];

		$response = $this->post('http://favsphere.local/app/login', $data);

		// Vérifier que l'authentification échoue (pas de token retourné)
		$this->assertArrayHasKey('message', $response);
		$this->assertEquals('Données incorrectes ou manquantes.', $response['message']);
	}

	public function testXssAttack()
	{
		// Insertion de script dans le champ username et password
		$data = [
			'username' => '<script>alert("XSS")</script>',
			'password' => '<script>alert("XSS")</script>'
		];

		$response = $this->post('http://favsphere.local/app/login', $data);

		// Vérifier que la réponse ne contient pas le script malveillant
		$this->assertNotContains('<script>', $response, 'Le système est vulnérable au XSS.');
	}
}
