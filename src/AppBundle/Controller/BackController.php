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
		$permissions = ['email'];
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
			$this->getDoctrine()->getManager()->persist($quizz);
			$this->getDoctrine()->getManager()->flush();
		}

		return $this->redirect($this->generateUrl("new_question"));
	}

	/**
	 * @Route("/userDatas", name="user_datas")
	 */
	public function displayUserDatasAction()
	{

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
		$question->setQuizz($this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($idQuizz));

		if($idQuestion !== null)
		{
			$question = $this->getDoctrine()->getManager()->getRepository('AppBundle:Question')->find($idQuestion);
		}
		$form = $this->createForm(new QuestionType(), $question);

		return $this->render(":back:new_question.html.twig", array(
			"form" => $form->createView()
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
		}

		return "ok";
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