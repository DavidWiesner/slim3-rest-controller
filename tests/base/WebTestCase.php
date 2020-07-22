<?php
/**
 * Created by IntelliJ IDEA.
 * User: david
 * Date: 18.03.16
 * Time: 07:16
 */
namespace DBohoTest\Slim\Controller\base;

use PDO;
use PHPUnit\Framework\TestCase;
use Slim\App;

class WebTestCase extends TestCase
{
	protected static $sql;
	/**
	 * @var App
	 */
	protected $app;
	/**
	 * @var WebTestClient
	 */
	protected $client;

	/**
	 * @var PDO
	 */
	protected $pdo;

	// Run for each unit test to setup our slim app environment
	protected function setUp()
	{
		// Establish a local reference to the Slim app object
		$this->app = $this->getSlimInstance();
		$this->client = new WebTestClient($this->app);
		$this->pdo = $this->app->getContainer()->get('pdo');
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setupTable();
	}

	public function getSlimInstance()
	{
		$app = new App([]);
		$container = $app->getContainer();
		$container['pdo'] = function ($c) {
			return new PDO('sqlite::memory:', '', '');
		};
		return $app;
	}

	protected function setupTable()
	{

	}
}
