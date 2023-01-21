<?php

namespace NetBull\CoreBundle\Routing;

use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class Extractor implements ExtractorInterface
{
    /**
     * @var RouterInterface
     */
    protected RouterInterface $router;

    /**
     * @var string
     */
    protected string $cacheDir;

    /**
     * @var array
     */
    protected array $bundles;

    /**
     * @param RouterInterface $router
     * @param string $cacheDir
     * @param array $bundles
     */
    public function __construct(RouterInterface $router, string $cacheDir, array $bundles = [])
    {
        $this->router = $router;
        $this->cacheDir = $cacheDir;
        $this->bundles = $bundles;
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes(): RouteCollection
    {
        $collection = $this->router->getRouteCollection();
        $routes = new RouteCollection();

        foreach ($collection->all() as $name => $route) {
            if ($this->isRouteExposed($route)) {
                $routes->add($name, $route);
            }
        }
        return $routes;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->router->getContext()->getBaseUrl() ?: '';
    }

    /**
     * @return string
     */
    public function getHost(): string
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
     * @return string
     */
    public function getScheme(): string
    {
        return $this->router->getContext()->getScheme();
    }

    /**
     * @return string
     */
    public function getCachePath(): string
    {
        $cachePath = $this->cacheDir . DIRECTORY_SEPARATOR . 'netbullCore';
        if ( !file_exists($cachePath) ) {
            mkdir($cachePath);
        }

        return $cachePath . DIRECTORY_SEPARATOR . 'data.json';
    }

    /**
     * @return ResourceInterface[]
     */
    public function getResources(): array
    {
        return $this->router->getRouteCollection()->getResources();
    }

    /**
     * @param Route $route
     *
     * @return bool
     */
    public function isRouteExposed( Route $route ): bool
    {
        return true === $route->getOption('expose')
            || 'true' === $route->getOption('expose');
    }

    /**
     * Check whether server is serving this request from a non-standard port
     *
     * @return bool
     */
    private function usesNonStandardPort(): bool
    {
        return $this->usesNonStandardHttpPort() || $this->usesNonStandardHttpsPort();
    }

    /**
     * Check whether server is serving HTTP over a non-standard port
     *
     * @return bool
     */
    private function usesNonStandardHttpPort(): bool
    {
        return 'http' === $this->getScheme() && '80' != $this->router->getContext()->getHttpPort();
    }

    /**
     * Check whether server is serving HTTPS over a non-standard port
     *
     * @return bool
     */
    private function usesNonStandardHttpsPort(): bool
    {
        return 'https' === $this->getScheme() && '443' != $this->router->getContext()->getHttpsPort();
    }
}
