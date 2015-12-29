<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
	    $fbServ = $this->container->get('facebook_service');

	    $fb = $fbServ->fbLogger();

        return $this->render('default/index.html.twig', array(
            'base_dir' => ''
        ));
    }
}
