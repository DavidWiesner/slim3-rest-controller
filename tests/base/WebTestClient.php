<?php
namespace DBohoTest\Slim\Controller\base;

use BadMethodCallException;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;


/**
 * Class WebTestClient
 * @method ResponseInterface get(string $path, mixed $data = '', array $headers = [])
 * @method ResponseInterface post(string $path, mixed $data = '', array $headers = [])
 * @method ResponseInterface patch(string $path, mixed $data = '', array $headers = [])
 * @method ResponseInterface put(string $path, mixed $data = '', array $headers = [])
 * @method ResponseInterface delete(string $path, mixed $data = '', array $headers = [])
 * @method ResponseInterface head(string $path, mixed $data = '', array $headers = [])
 */
class WebTestClient
{
    /**
     * @var App
     */
    public $app;
    /**
     * @var Request
     */
    public $request;
    /**
     * @var Response
     */
    public $response;
    // We support these methods for testing. These are available via
    // `this->get()` and `$this->post()`. This is accomplished with the
    // `__call()` magic method below.
    public $testingMethods = array('get', 'post', 'patch', 'put', 'delete', 'head');

    public function __construct(App $slim)
    {
        $this->app = $slim;
    }

    // Implement our `get`, `post`, and other http operations
    public function __call($method, $arguments)
    {
        if (in_array($method, $this->testingMethods)) {
            list($path, $data, $headers) = array_pad($arguments, 3, array());
            return $this->request($method, $path, $data, $headers);
        }
        throw new BadMethodCallException(strtoupper($method) . ' is not supported');
    }
    // Abstract way to make a request to SlimPHP, this allows us to mock the
    // slim environment
    private function request($method, $path, $data = array(), $optionalHeaders = array())
    {
        $req = $this->createRequest($method, $path, $data, $optionalHeaders);
        // Prepare a mock environment
        // Establish some useful references to the slim app properties
        $this->request = $req;
        $this->response = $res = new Response();
        // Execute our app
        $app = $this->app;
        $this->response = $app($req, $res);
        return $this->response;
    }

    /**
     * @param $method
     * @param $path
     * @param $data
     * @param array $optionalHeaders
     * @return Request
     */
    public static function createRequest($method, $path, $data = null, $optionalHeaders = array())
    {
        $query = parse_url($path, PHP_URL_QUERY);
        $options = array(
                'REQUEST_METHOD' => strtoupper($method),
                'REQUEST_URI' => $path,
                'SERVER_NAME' => 'local.dev',
        );
        if ($query !== false) {
            $options['QUERY_STRING'] = $query;
        }
        $options = array_replace_recursive($options, $optionalHeaders);
        $env = Environment::mock($options);
        $uri = Uri::createFromEnvironment($env);
        $headers = Headers::createFromEnvironment($env);
        $cookies = [];
        $serverParams = $env->all();
        $body = new RequestBody();
        if ($data != null and $method !== 'get') {
            if (is_array($data)) {
                $body->write(http_build_query($data));
            } else {
                $body->write($data);
            }
            $body->rewind();
        }
        $req = new Request(strtoupper($method), $uri, $headers, $cookies, $serverParams, $body);
        if ($method === 'get') {
            $req->withAttributes($data);
            $req = $req->withQueryParams($data);
            return $req;
        } elseif ($data != null) {
            $req = $req->withParsedBody($data);
        }
        return $req;
    }
}

