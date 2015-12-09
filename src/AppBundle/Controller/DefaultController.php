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

	    $item = $this->getDoctrine()->getManager()->getRepository('AppBundle:Config')->find(1);

        return $this->render('default/index.html.twig', array(
            'base_dir' => $item->getThemeColor()
        ));
    }
}
