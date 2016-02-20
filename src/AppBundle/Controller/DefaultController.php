<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Facebook;
use AppBundle\Entity\Resultat;

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
		}
		else if ($userToken == 'error')
		{
			return $this->render('default/index.html.twig', array(
				'testString' => 'error'
			));
		}
		else
		{
			try
			{
				$user = $this->container->get('facebook_service')->getFBUser($userToken);
				$session->set('fbId',$user->getId());
			}
			catch(Facebook\Exceptions\FacebookSDKException $e) {
				return $this->redirect($this->generateUrl('login'));
			}
			$datas = $this->prepareHome();
			return $this->render('default/index.html.twig', $datas);
		}
	}

	private function prepareHome()
	{
		$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->getCurrentQuizz();
		$lastQuizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->getLastQuizz();

		$participated = false;
		$lastParticipation = false;
		$user = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users')->findOneByIdFacebook($this->get('session')->get('fbId'));

		if($quizz != null)
		{
			$resultCurrentUser = $this->getDoctrine()->getManager()->getRepository('AppBundle:Resultat')->findOneBy(array('quizz'=> $quizz->getId(),'user'=>$user->getId()));
			if(is_object($resultCurrentUser) && $resultCurrentUser->getTimeStart() != $resultCurrentUser->getResponseTime())
			{
				$participated = true;
			}
		}

		if($lastQuizz != null)
		{
			$lastrResultCurrentUser = $this->getDoctrine()->getManager()->getRepository('AppBundle:Resultat')->findOneBy(array('quizz'=> $lastQuizz->getId(),'user'=>$user->getId()));
			if(is_object($lastrResultCurrentUser))
			{
				$lastParticipation = $lastrResultCurrentUser;
			}
		}

		return array(
			'quizz' => $quizz,
			'lastQuizz' => $lastQuizz,
	        'participated' => $participated,
            'lastParticipation' => $lastParticipation
			);
	}

	/**
	 * @Route("/policies", name="app_policies")
	 */
	public function displayPoliciesAction()
	{
		return $this->render('front/policies.html.twig');
	}

	/**
	 * @Route("/startQuizz/{id}", name="start_quizz")
	 */
	public function startQuizzAction($id)
	{
		$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($id);
		$user = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users')->findOneByIdFacebook($this->get('session')->get('fbId'));

		$resultat = new Resultat();
		$resultat->setQuizz($quizz);
		$resultat->setUser($user);

		$this->getDoctrine()->getManager()->persist($resultat);
		$this->getDoctrine()->getManager()->flush();

		return $this->render('front/quizz.html.twig',array(
			'quizz' => $quizz
		));
	}

	/**
	 * @Route("/nextQuestion/{idQuizz}/{nbQuestion}", name="next_question")
	 */
	public function nextQuestionAction($idQuizz,$nbQuestion)
	{
		$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($idQuizz);
		$questions = $quizz->getQuestions();

		if(isset($questions[$nbQuestion+1]))
		{
			$nextQuestion = $nbQuestion+1;
		}
		else
		{
			$nextQuestion = -1;
		}

		return $this->render('front/question.html.twig',array(
			'question' => $questions[$nbQuestion],
			'nbQuestion' => $nextQuestion
		));
	}

	/**
	 * @Route("/submitQuizz/{idQuizz}", name="submit_quizz")
	 */
	public function submitQuizzAction($idQuizz)
	{
		$now = new \DateTime();
		$request = $this->get('request');

		$responses = $request->get('responses');

		$score = $this->calculScore($responses);

		$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($idQuizz);
		$user = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users')->findOneByIdFacebook($this->get('session')->get('fbId'));
		$result = $this->getDoctrine()->getManager()->getRepository('AppBundle:Resultat')->findOneBy(array('quizz'=> $quizz->getId(),'user'=>$user->getId()));

		$result->setScore($score);
		$result->setResponseTime($now);

		$this->getDoctrine()->getManager()->persist($result);
		$this->getDoctrine()->getManager()->flush();

		return $this->render('front/quizzFinal.html.twig');
	}

	/**
	 * @Route("/generalClassement", name="general_classement")
	 */
	public function generalClassementAction()
	{
		return $this->render('front/policies.html.twig');
	}
}
