<?php
namespace DBohoTest\Slim\Controller;

use DBoho\IO\DataAccess;
use DBoho\Slim\Controller\TableController;
use DBohoTest\Slim\Controller\base\WebTestCase;
use PDOException;
use Psr\Log\LoggerInterface;
use Slim\Http\Response;

class TableControllerTest extends WebTestCase
{
    /**
     * @var DataAccess
     */
    protected $dataaccess;
    /**
     * @var TableController
     */
    private $controller;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var PHPUnit\
     */
    private $dataAccessMock;

    public function testGetAllException()
    {
        $this->dataAccessMock->expects($this->once())->method('select')->willThrowException(new PDOException());
        $request = $this->client->createRequest('GET', '/noauth/api/books');

        $response = $this->controller->getAll($request, new Response(), ['table' => 'books']);

        $json = json_decode($response->getBody());
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertSame(null, $json);
    }

    public function testAppGetAll()
    {
        $response = $this->client->get('/noauth/api/books');

        $json = json_decode($response->getBody(), 1);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame([
                ['id' => '1', 'title' => 'last', 'price' => '2'],
                ['id' => '2', 'title' => 'first', 'price' => '1'],
        ], $json);
    }

    public function testAppGetOne()
    {
        $response = $this->client->get('/noauth/api/books/1');

        $json = json_decode($response->getBody(), 1);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame([
                ['id' => '1', 'title' => 'last', 'price' => '2'],
        ], $json);
    }

    public function testSelectException()
    {
        $this->dataAccessMock->expects($this->once())->method('select')->willThrowException(new PDOException());
        $request = $this->client->createRequest('GET', '/noauth/api/books');

        $response = $this->controller->get($request, new Response(), ['table' => 'books']);

        $json = json_decode($response->getBody());
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertSame(null, $json);
    }


    public function testAppSelectQuery()
    {
        $response = $this->client->get('/noauth/api/books', ['title' => 'first']);

        $json = json_decode($response->getBody(), 1);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame([
                ['id' => '2', 'title' => 'first', 'price' => '1'],
        ], $json);
    }

    public function testAppInsertMissingRequired()
    {
        $response = $this->client->post('/noauth/api/books', ['title' => 'first']);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testAppInsertMoreRequired()
    {
        $response = $this->client->post('/noauth/api/books', ['title' => 'new', 'price' => 3, 'noColumn' => 'none']);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('/noauth/api/books/3', $response->getHeader('Location')[0]);
    }

    public function testUpdateOne()
    {
        $response = $this->client->put('/noauth/api/books/1', ['price' => '4.00']);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $this->dataaccess->select('books');
        $this->assertSame([
                        ['id' => '1', 'title' => 'last', 'price' => '4'],
                        ['id' => '2', 'title' => 'first', 'price' => '1'],
                ], $data);
    }

    public function testUpdateMultiple()
    {
        $response = $this->client->put('/noauth/api/books', ['price' => '4.00']);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $this->dataaccess->select('books');
        $this->assertSame([
                ['id' => '1', 'title' => 'last', 'price' => '4'],
                ['id' => '2', 'title' => 'first', 'price' => '4'],
        ], $data);
    }

    public function testUpdateQuerySelect()
    {
        $response = $this->client->put('/noauth/api/books?id=1', ['price' => '4.00']);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $this->dataaccess->select('books');
        $this->assertSame([
                ['id' => '1', 'title' => 'last', 'price' => '4'],
                ['id' => '2', 'title' => 'first', 'price' => '1'],
        ], $data);
    }

    public function testUpdateNotFound()
    {
        $response = $this->client->put('/noauth/api/books/4', ['price' => '4.00']);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testUpdateException()
    {
        $this->dataAccessMock->expects($this->once())->method('update')->willThrowException(new PDOException());
        $request = $this->client->createRequest('PUT', '/noauth/api/books/1');

        $response = $this->controller->update($request, new Response(), ['table' => 'books']);

        $json = json_decode($response->getBody());
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertSame(null, $json);
    }

    public function testDeleteOne()
    {
        $response = $this->client->delete('/noauth/api/books/2');

        $this->assertEquals(204, $response->getStatusCode());
        $data = $this->dataaccess->select('books');
        $this->assertSame([['id' => '1', 'title' => 'last', 'price' => '2']], $data);

    }

    public function testDeleteAll()
    {
        $response = $this->client->delete('/noauth/api/books');

        $this->assertEquals(204, $response->getStatusCode());
        $data = $this->dataaccess->select('books');
        $this->assertSame([], $data);

    }

    public function testDeleteByQuery()
    {
        $response = $this->client->delete('/noauth/api/books?id=2');

        $this->assertEquals(204, $response->getStatusCode());
        $data = $this->dataaccess->select('books');
        $this->assertSame([['id' => '1', 'title' => 'last', 'price' => '2']], $data);
    }

    public function testDeleteNotFound()
    {
        $response = $this->client->delete('/noauth/api/books/4');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteException()
    {
        $this->dataAccessMock->expects($this->once())->method('delete')->willThrowException(new PDOException());
        $request = $this->client->createRequest('DELETE', '/noauth/api/books/1');

        $response = $this->controller->delete($request, new Response(), ['table' => 'books']);

        $json = json_decode($response->getBody());
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertSame(null, $json);
    }



    protected function setUp()
    {
        parent::setUp();

        $container = $this->app->getContainer();
        $this->dataaccess = new DataAccess($this->pdo);
        $container[TableController::class] = function ($c) {
            return new TableController($this->dataaccess, $this->logger);
        };
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->dataAccessMock = $this->getMockBuilder(DataAccess::class)
            ->setConstructorArgs([$this->pdo])->getMock();//->disableOriginalConstructor()->getMock();
        $this->controller = new TableController($this->dataAccessMock, $this->logger);
        $this->app->group('/noauth/api/{table:books}', function () {
            $this->get('', TableController::class . ':getAll');
            $this->get('/{id:[0-9]+}', TableController::class . ':get');
            $this->post('', TableController::class . ':add');
            $this->put('/{id:[0-9]+}', TableController::class . ':update');
            $this->put('', TableController::class . ':update');
            $this->delete('/{id:[0-9]+}', TableController::class . ':delete');
            $this->delete('', TableController::class . ':delete');
        });

        $this->pdo->exec('CREATE TABLE books ( id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
											title varchar(200) NOT NULL, price int(11) NOT NULL);
						  INSERT INTO `books` (`id`, `title`, `price`) VALUES (1, "last", 2), (2, "first", 1);
						  CREATE TABLE `test``escp` (`1``st` INT, `2````st` INT NOT NULL);
						  INSERT INTO `test``escp` (`1``st`, `2````st`) VALUES (1,2), (2, 1)');
    }
}
