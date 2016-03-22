<?php

namespace DBoho\Slim\Controller;

use DBoho\IO\DataAccess;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class TableController.
 */
class TableController
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var DataAccess
     */
    protected $dataaccess;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param DataAccess $dataaccess
     */
    public function __construct(LoggerInterface $logger, DataAccess $dataaccess)
    {
        $this->logger = $logger;
        $this->dataaccess = $dataaccess;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getAll(Request $request, Response $response, $args)
    {
        $this->logCall(__FUNCTION__);
        $path = $args['table'];
        $params = $request->getParams();
        try {
            $result = $this->dataaccess->select($path, [], $params);
        } catch (\PDOException $exception) {
            $this->logException($exception);
            return $response->withStatus(400, $exception->getMessage());
        }
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get(Request $request, Response $response, $args)
    {
        $this->logCall(__FUNCTION__);

        $path = $args['table'];

        try {
            $result = $this->dataaccess->select($path, [], $args);
        } catch (\PDOException $exception) {
            $this->logException($exception);
            return $response->withStatus(400, $exception->getMessage());
        }
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function add(Request $request, Response $response, $args)
    {
        $this->logCall(__FUNCTION__);

        $path = $args['table'];
        $request_data = $request->getParsedBody();

        try {
            $last_inserted_id = $this->dataaccess->insert($path, $request_data);
        } catch (\PDOException $exception) {
            $this->logException($exception);
            return $response->withStatus(403);
        }
        $uri = $request->getUri();
        $LocationHeader = $uri->withPath($uri->getPath() . '/' . (string)$last_inserted_id);
        return $response->withHeader('Location', $LocationHeader->getPath())->withStatus(201);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(Request $request, Response $response, $args)
    {
        $this->logCall(__FUNCTION__);

        $path = $args['table'];
        $data = $request->getParsedBody();

        try{
            $filter = array_merge($request->getQueryParams(), $args);
            $affectedRows = $this->dataaccess->update($path, $data, $filter);
            if($affectedRows === 0){
                return $response->withStatus(404);
            }
            return $response->withStatus(200);
        } catch (\PDOException $exception) {
            $this->logException($exception);
            return $response->withStatus(400);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete(Request $request, Response $response, $args)
    {
        $this->logger->info(substr(strrchr(rtrim(__CLASS__, '\\'), '\\'), 1) . ': ' . __FUNCTION__);
        $path = $args['table'];
        try{
            $filter = array_merge($request->getQueryParams(), $args);
            $affectedRows = $this->dataaccess->delete($path, $filter);
            if ($affectedRows) {
                return $response->withStatus(204);
            } else {
                return $response->withStatus(404);
            }
        } catch(\PDOException $exception){
            $this->logException($exception);
            return $response->withStatus(400);
        }
    }

    /**
     * @param $func
     */
    private function logCall($func)
    {
        $this->logger->info(substr(strrchr(rtrim(__CLASS__, '\\'), '\\'), 1) . ': ' . $func);
    }

    private function logException(\PDOException $e){
        $this->logger->warning(substr(strrchr(rtrim(__CLASS__, '\\'), '\\'), 1) . ': ' . $e->getMessage());

    }
}
