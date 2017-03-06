<?php

namespace Netbull\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('NetbullCoreBundle:Default:index.html.twig');
    }
}
