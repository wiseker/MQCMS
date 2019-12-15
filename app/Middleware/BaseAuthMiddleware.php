<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Utils\Common;
use App\Utils\JWT;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BaseAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */

    protected $response;

    /**
     * @var string
     */
    protected $header = 'Authorization';

    /**
     * @var string
     */
    protected $pattern = '/^Bearer\s+(.*?)$/';

    /**
     * @var string
     */
    protected $realm = 'api';

    /**
     * @var string
     */
    public static $authToken = '';

    /**
     * @var array
     */
    public static $tokenInfo = [];

    /**
     * AuthMiddleware constructor.
     * @param ContainerInterface $container
     * @param HttpResponse $response
     * @param RequestInterface $request
     */
    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * get jwt config
     * @param $currentPath
     * @return array
     */
    public static function getJwtConfig(RequestInterface $request)
    {
        $currentPath = Common::getCurrentPath($request);
        return [
            'key' => env('JWT_' . strtoupper($currentPath) . '_KEY', 'JWT_API_KEY'),
            'id' => env('JWT_' . strtoupper($currentPath) . '_ID', 'JWT_API_ID'),
            'exp' => env('JWT_' . strtoupper($currentPath) . '_EXP', 'JWT_API_EXP'),
            'aud' => env('JWT_' . strtoupper($currentPath) . '_AUD', 'JWT_API_AUD'),
            'iss' => env('JWT_' . strtoupper($currentPath) . '_ISS', 'JWT_API_ISS')
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->challenge();
        $header = $request->getHeader($this->header);
        $tokenInfo = $this->authenticate($header);
        $uid = $tokenInfo && $tokenInfo['id'] ? $tokenInfo['id'] : 0;
        $request = $request->withAttribute('uid', $uid);
        Context::set(ServerRequestInterface::class, $request);
        return $handler->handle($request);
    }

    /**
     * setHeader
     */
    public function challenge()
    {
        $response = $this->response->withHeader('WWW-Authenticate', "Bearer realm=\"{$this->realm}\"");
        Context::set(ResponseInterface::class, $response);
    }

    /**
     * 验证token 必须加Bearer 或者其他头部
     * @param $header
     * @return bool|null
     */
    public function authenticate($header)
    {
        if (!empty($header) && $header[0] !== null) {
            if ($this->pattern === null) {
                return null;
            }
            if (preg_match($this->pattern, $header[0], $matches)) {
                self::$authToken = $matches[1];
                return $this->getAuthTokenInfo();
            }
            return null;
        }
        return null;
    }

    /**
     * 验证token有效性并获取token值
     * @param RequestInterface $request
     * @return array|bool|object|string
     */
    public function getAuthTokenInfo()
    {
        $config = self::getJwtConfig($this->request);
        self::$tokenInfo = JWT::getTokenInfo(self::$authToken, $config);
        return self::$tokenInfo;
    }

    /**
     * 创建token
     * @param $info
     * @return string
     */
    public static function createAuthToken($info, RequestInterface $request)
    {
        return JWT::createToken($info, self::getJwtConfig($request));
    }
}