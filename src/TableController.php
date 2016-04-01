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
    protected $dataAccess;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param DataAccess $dataAccess
     */
    public function __construct(DataAccess $dataAccess, LoggerInterface $logger = null)
    {
        $this->dataAccess = $dataAccess;
        $this->logger = $logger;
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
            $result = $this->dataAccess->select($path, [], $params);
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
            $result = $this->dataAccess->select($path, [], $args);
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
        $request_data = $this->getAddData($args['table'], $request, $args);

        try {
            $this->dataAccess->insert($path, $request_data);
            $last_inserted_id = $this->dataAccess->getLastInsertId();
        } catch (\PDOException $exception) {
            $this->logException($exception);
            return $response->withStatus(400);
        }
        $uri = $request->getUri();
        $LocationHeader = $uri->withPath($uri->getPath() . '/' . (string)$last_inserted_id);
        return $response->withHeader('Location', $LocationHeader->getPath())->withStatus(201);
    }

    /**
     * @param string $table
     * @param Request $request
     * @param array $args
     * @return array|null|object
     */
    protected function getAddData($table, Request $request, array $args)
    {
        $data = $request->getParsedBody();
        return $data;
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
        $data = $this->getUpdateData($args['table'], $request, $args);

        try {
            $filter = array_merge($request->getQueryParams(), $args);
            $affectedRows = $this->dataAccess->update($path, $data, $filter);
            if ($affectedRows === 0) {
                return $response->withStatus(404);
            }
            return $response->withStatus(200);
        } catch (\PDOException $exception) {
            $this->logException($exception);
            return $response->withStatus(400);
        }
    }

    /**
     * @param string $table
     * @param Request $request
     * @param array $args
     * @return array|null|object
     */
    protected function getUpdateData($table, Request $request, array $args)
    {
        $data = $request->getParsedBody();
        return $data;
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
        try {
            $filter = array_merge($request->getQueryParams(), $args);
            $affectedRows = $this->dataAccess->delete($path, $filter);
            if ($affectedRows) {
                return $response->withStatus(204);
            } else {
                return $response->withStatus(404);
            }
        } catch (\PDOException $exception) {
            $this->logException($exception);
            return $response->withStatus(400);
        }
    }

    /**
     * @param $func
     */
    private function logCall($func)
    {
        if($this->logger != null){
            $this->logger->info(substr(strrchr(rtrim(__CLASS__, '\\'), '\\'), 1) . ': ' . $func);
        }
    }

    private function logException(\PDOException $e)
    {
        if($this->logger != null) {
            $this->logger->warning(substr(strrchr(rtrim(__CLASS__, '\\'), '\\'), 1) . ': ' . $e->getMessage());
        }
    }
}
