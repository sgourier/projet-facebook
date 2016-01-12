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
		$session = $this->get('session');
		$userToken = $session->get('facebook_access_token', null);

		if ($userToken == null) {
			return $this->redirect($this->generateUrl('login'));
		} else if ($userToken == 'error') {
			return $this->render('default/index.html.twig', array(
				'testString' => 'error'
			));
		} else {
			$user = $this->container->get('facebook_service')->getFBUser($userToken);
			return $this->render('default/index.html.twig', array(
				'testString' => $user['name']
			));
		}
	}
}
