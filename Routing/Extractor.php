<?php

namespace Netbull\CoreBundle\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class Extractor
 * @package Netbull\CoreBundle\Routing
 */
class Extractor
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Base cache directory
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * @var array
     */
    protected $bundles;

    /**
     * Default constructor.
     *
     * @param RouterInterface $router         The router.
     * @param string          $cacheDir
     * @param array           $bundles        list of loaded bundles to check when generating the prefix
     */
    public function __construct( RouterInterface $router, $cacheDir, $bundles = [] )
    {
        $this->router         = $router;
        $this->cacheDir       = $cacheDir;
        $this->bundles        = $bundles;
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes()
    {
        $collection = $this->router->getRouteCollection();
        $routes     = new RouteCollection();
        /** @var Route $route */
        foreach ( $collection->all() as $name => $route ) {
            if ($this->isRouteExposed($route)) {
                $routes->add($name, $route);
            }
        }
        return $routes;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->router->getContext()->getBaseUrl() ?: '';
    }

    /**
     * @return string
     */
    public function getHost()
    {
        $requestContext = $this->router->getContext();
        $host = $requestContext->getHost();

        if ($this->usesNonStandardPort()) {
            $method = sprintf('get%sPort', ucfirst($requestContext->getScheme()));
            $host .= ':' . $requestContext->$method();
        }

        return $host;
    }

    /**
     * @return mixed
     */
    public function getScheme()
    {
        return $this->router->getContext()->getScheme();
    }

    /**
     * @return string
     */
    public function getCachePath()
    {
        $cachePath = $this->cacheDir . DIRECTORY_SEPARATOR . 'netbullCore';
        if ( !file_exists($cachePath) ) {
            mkdir($cachePath);
        }

        $cachePath = $cachePath . DIRECTORY_SEPARATOR . 'data.json';

        return $cachePath;
    }

    /**
     * @return \Symfony\Component\Config\Resource\ResourceInterface[]
     */
    public function getResources()
    {
        return $this->router->getRouteCollection()->getResources();
    }

    /**
     * @param Route $route
     *
     * @return bool
     */
    public function isRouteExposed( Route $route )
    {
        return true === $route->getOption('expose')
            || 'true' === $route->getOption('expose');
    }

    /**
     * Check whether server is serving this request from a non-standard port
     *
     * @return bool
     */
    private function usesNonStandardPort()
    {
        return $this->usesNonStandardHttpPort() || $this->usesNonStandardHttpsPort();
    }

    /**
     * Check whether server is serving HTTP over a non-standard port
     *
     * @return bool
     */
    private function usesNonStandardHttpPort()
    {
        return 'http' === $this->getScheme() && '80' != $this->router->getContext()->getHttpPort();
    }

    /**
     * Check whether server is serving HTTPS over a non-standard port
     *
     * @return bool
     */
    private function usesNonStandardHttpsPort()
    {
        return 'https' === $this->getScheme() && '443' != $this->router->getContext()->getHttpsPort();
    }
}
