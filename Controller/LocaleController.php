<?php

namespace NetBull\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use NetBull\CoreBundle\Locale\Events;
use NetBull\CoreBundle\Event\FilterLocaleSwitchEvent;
use NetBull\CoreBundle\Locale\Validator\MetaValidator;

/**
 * Class LocaleController
 * @package NetBull\CoreBundle\Controller
 */
class LocaleController
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var null|RouterInterface
     */
    private $router;

    /**
     * @var MetaValidator
     */
    private $metaValidator;

    /**
     * @var bool
     */
    private $useReferrer;

    /**
     * @var null
     */
    private $redirectToRoute;

    /**
     * @var string
     */
    private $statusCode;

    /**
     * LocaleController constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param RouterInterface|null $router
     * @param MetaValidator $metaValidator
     * @param bool $useReferrer
     * @param null $redirectToRoute
     * @param string $statusCode
     */
    public function __construct(EventDispatcherInterface $dispatcher, RouterInterface $router = null, MetaValidator $metaValidator, $useReferrer = true, $redirectToRoute = null, $statusCode = '302')
    {
        $this->dispatcher = $dispatcher;
        $this->router = $router;
        $this->metaValidator = $metaValidator;
        $this->useReferrer = $useReferrer;
        $this->redirectToRoute = $redirectToRoute;
        $this->statusCode = $statusCode;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function switch(Request $request)
    {
        $_locale = $request->attributes->get('_locale', $request->getLocale());
        $statusCode = $request->attributes->get('statusCode', $this->statusCode);
        $useReferrer = $request->attributes->get('useReferrer', $this->useReferrer);
        $redirectToRoute = $request->attributes->get('route', $this->redirectToRoute);
        $metaValidator = $this->metaValidator;
        if (!$metaValidator->isAllowed($_locale)) {
            throw new \InvalidArgumentException(sprintf('Not allowed to switch to locale %s', $_locale));
        }

        $localeSwitchEvent = new FilterLocaleSwitchEvent($request, $_locale);
        $this->dispatcher->dispatch(Events::onLocaleChange, $localeSwitchEvent);

        // Redirect the User
        if ($useReferrer && $request->headers->has('referer')) {
            $response = new RedirectResponse($request->headers->get('referer'), $statusCode);
        } elseif ($this->router && $redirectToRoute) {
            $target = $this->router->generate($redirectToRoute, array('_locale' => $_locale));
            if ($request->getQueryString()) {
                if (!strpos($target, '?')) {
                    $target .= '?';
                }
                $target .= $request->getQueryString();
            }
            $response = new RedirectResponse($target, $statusCode);
        } else {
            // TODO: this seems broken, as it will not handle if the site runs in a subdir
            // TODO: also it doesn't handle the locale at all and can therefore lead to an infinite redirect
            $response = new RedirectResponse($request->getScheme() . '://' . $request->getHttpHost() . '/', $statusCode);
        }
        return $response;
    }
}
