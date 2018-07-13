<?php

namespace NetBull\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use NetBull\CoreBundle\Locale\Switcher\TargetInformationBuilder;

/**
 * Class LocaleSwitcherExtension
 * @package NetBull\CoreBundle\Twig
 */
class LocaleSwitcherExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * LocaleSwitcherExtension constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array The added functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('locale_switcher', [$this, 'renderSwitcher'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param null $route
     * @param array $parameters
     * @param null $template
     * @return mixed
     * @throws \Exception
     */
    public function renderSwitcher($route = null, $parameters = [], $template = null)
    {
        $showCurrentLocale = $this->container->getParameter('netbull_core.locale.switcher.show_current_locale');
        $useController = $this->container->getParameter('netbull_core.locale.switcher.use_controller');
        $allowedLocales = $this->container->get('netbull_core.allowed_locales_provider')->getAllowedLocales();
        $request = $this->container->get('request_stack')->getMasterRequest();
        $router = $this->container->get('router');
        $infoBuilder = new TargetInformationBuilder($request, $router, $allowedLocales, $showCurrentLocale, $useController);
        $info = $infoBuilder->getTargetInformation($route, $parameters);

        return $this->container->get('netbull_core.locale_switcher_helper')->renderSwitch($info, $template);
    }

    /**
     * @return string The name of the extension
     */
    public function getName()
    {
        return 'locale_switcher';
    }
}
