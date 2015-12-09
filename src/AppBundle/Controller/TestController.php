<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
/**
 * @Route("/test")
 */
class TestController extends Controller
{
    /**
     * @Route("/view", name="homepage")
     */
    public function viewAction($view)
    {
        return $this->render('test/'$view.'.html.twig'));
    }
}
