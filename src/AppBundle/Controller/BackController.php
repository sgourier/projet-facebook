<?php
/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 11/01/2016
 * Time: 19:05
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Facebook;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Form\QuizzType;
use AppBundle\Entity\Quizz;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\QuestionType;
use AppBundle\Entity\Question;
use AppBundle\Entity\Reponse;

class BackController extends Controller
{
	/**
	 * @Route("/login", name="login")
	 */
	public function loginAction()
	{
		$fbServ = $this->container->get('facebook_service');

		$fb = $fbServ->fbLogger();

		$helper = $fb->getRedirectLoginHelper();
		$permissions = ['email','user_birthday'];
		$loginUrl = $helper->getLoginUrl($this->generateUrl('login_callback',array(),UrlGeneratorInterface::ABSOLUTE_URL), $permissions);

		return $this->redirect($loginUrl);
	}

	/**
	 * @Route("/loginCallback", name="login_callback")
	 */
	public function loginCallbackAction()
	{
		$fbServ = $this->container->get('facebook_service');
		$fb = $fbServ->fbLogger();
		$session = $this->get('session');
		$session->set('facebook_access_token', 'error');

		$helper = $fb->getRedirectLoginHelper();

		try {
			$accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		if (isset($accessToken)) {
			// Logged in!
			$session->set('facebook_access_token', (string) $accessToken);
			$fbServ->saveUser($accessToken);
			// Now you can redirect to another page and use the

			// access token from $_SESSION['facebook_access_token']
		}

		return $this->redirect($this->generateUrl('homepage'));
	}

	/**
	 * @Route("/backMenu", name="back_menu")
	 */
	public function backMenuAction()
	{
		return $this->render('back/backMenu.html.twig');
	}

	/**
	 * @Route("/newQuizz/{idQuizz]", name="new_quizz", defaults={"idQuizz" = null})
	 */
	public function newQuizzAction($idQuizz)
	{
		$quizz = new Quizz();
		if($idQuizz !== null)
		{
			$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($idQuizz);
		}
		$quizzForm = $this->createForm(new QuizzType(),$quizz,array('method'=>'POST', 'action' => $this->generateUrl('save_quizz')));

		return $this->render('back/quizzForm.html.twig',array(
			'form' => $quizzForm->createView()
		));
	}

	/**
	 * @Route("/saveQuizz", name="save_quizz")
	 * @Method({"POST"})
	 */
	public  function  saveQuizzAction()
	{
		$quizz = new Quizz;

		$formQuizz = $this->createForm(new QuizzType(),$quizz);

		$formQuizz->handleRequest( $this->get( 'request' ) );
		if ( $formQuizz->isValid() )
		{
			$repoQuizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz');
			$dateStart = new \DateTime($formQuizz['datetimeStart']->getData());
			$dateEnd = new \DateTime($formQuizz['datetimeEnd']->getData());

			if($repoQuizz->verifyDates($dateStart,$dateEnd) == 0)
			{
				$quizz->setDateStart($dateStart);
				$quizz->setDateEnd($dateEnd);
				$this->getDoctrine()->getManager()->persist($quizz);
				$this->getDoctrine()->getManager()->flush();
			}
			else
			{
				return $this->render('back/quizzForm.html.twig',array(
					'form' => $formQuizz->createView(),
					'error' => true
				));
			}
		}

		return $this->redirect($this->generateUrl("new_question",array(
			'idQuizz' => $quizz->getId()
		)));
	}

	/**
	 * @Route("/userDatas", name="user_datas")
	 */
	public function displayUserDatasAction()
	{
		$users = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users')->findAll();

		return $this->render('back/displayUsers.html.twig',array(
			'users' => $users
		));
	}

	/**
	 * @Route("/expUserDatas", name="exp_user_data")
	 */
	public function exportUserDatasAction()
	{

	}

	/**
	 * @Route("/newQuestion/{idQuizz}/{idQuestion}", name="new_question", defaults={"idQuestion" = null})
	 */
	public function newQuestionAction($idQuizz,$idQuestion)
	{
		$question = new Question;
		$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($idQuizz);
		$question->setQuizz($quizz);

		if($idQuestion !== null)
		{
			$question = $this->getDoctrine()->getManager()->getRepository('AppBundle:Question')->find($idQuestion);
		}
		$form = $this->createForm(new QuestionType(), $question,array('action' => $this->generateUrl('save_question')));

		return $this->render(":back:new_question.html.twig", array(
			"form" => $form->createView(),
			'nbQuestion' => count($quizz->getQuestions()) +1
		));
	}

	/**
	 * @Route("/saveQuestion", name="save_question")
	 */
	public function saveQuestionAction()
	{
		$question = new Question();

		$formQuestion = $this->createForm(new QuestionType(),$question);

		$formQuestion->handleRequest( $this->get( 'request' ) );
		if ( $formQuestion->isValid() )
		{
			$this->getDoctrine()->getManager()->persist($question);
			$this->getDoctrine()->getManager()->flush();
			$this->saveResponses($formQuestion,$question);
		}

		return $this->redirect($this->generateUrl('new_question',array('idQuizz'=>$question->getQuizz()->getId())));
	}

	private function saveResponses($formQuestion,$question)
	{
		if($formQuestion['response1']->getData() != null)
		{
			$response1 = new Reponse;
			$response1->setDescription($formQuestion['response1']->getData());
			$response1->setValid($formQuestion['correct1']->getData());
			$response1->setQuestion($question);
			$this->getDoctrine()->getManager()->persist($response1);
		}
		if($formQuestion['response2']->getData() != null)
		{
			$response2 = new Reponse;
			$response2->setDescription($formQuestion['response2']->getData());
			$response2->setValid($formQuestion['correct2']->getData());
			$response2->setQuestion($question);
			$this->getDoctrine()->getManager()->persist($response2);
		}
		if($formQuestion['response3']->getData() != null)
		{
			$response3 = new Reponse;
			$response3->setDescription($formQuestion['response3']->getData());
			$response3->setValid($formQuestion['correct3']->getData());
			$response3->setQuestion($question);
			$this->getDoctrine()->getManager()->persist($response3);
		}
		if($formQuestion['response4']->getData() != null)
		{
			$response4 = new Reponse;
			$response4->setDescription($formQuestion['response4']->getData());
			$response4->setValid($formQuestion['correct4']->getData());
			$response4->setQuestion($question);
			$this->getDoctrine()->getManager()->persist($response4);
		}
		$this->getDoctrine()->getManager()->flush();
	}

	/**
	 * @Route("/oldQuizz", name="old_quizz")
	 */
	public function displayOldQuizzAction()
	{

	}

	/**
	 * @Route("/quizzRank/{idQuizz}", name="quizz_rank")
	 */
	public function displayQuizzRankingAction($idQuizz)
	{

	}

	/**
	 * @Route("/newAnswer", name="new_answer")
	 */
	public function newAnswer()
	{

	}

	/**
	 * @Route("/saveAnswer", name="save_answer")
	 */
	public function saveAnswer()
	{

	}
}