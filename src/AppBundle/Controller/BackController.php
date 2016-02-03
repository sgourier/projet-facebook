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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

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
			'form' => $quizzForm->createView(),
		    'createQuizz' => true
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
					'error' => true,
					'createQuizz' => true
				));
			}
		}

		return $this->redirect($this->generateUrl("new_question",array(
			'idQuizz' => $quizz->getId()
		)));
	}

	/**
	 * @Route("/userDatas/{offset}/{search}", name="user_datas")
	 */
	public function displayUserDatasAction($offset = 0,$search = null)
	{
		$limit = 50;
		if($this->get('request')->isMethod('POST'))
		{
			$search = $this->get('request')['search'];
		}

		$users = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users')->getLimitedUsers($offset,$search,$limit);
		$nbUser = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users')->countAllWithSearch($search);

		return $this->render('back/displayUsers.html.twig',array(
			'users' => $users,
			'search' => $search,
			'offset' => $offset,
			'nbUser' => $nbUser,
			'limit' => $limit,
			'listUser' => true
		));
	}

	/**
	 * @Route("/expUserDatas", name="exp_user_data")
	 */
	public function exportUserDatasAction()
	{
		$em = $this->getDoctrine()->getEntityManager();

		$iterableResult = $em->getRepository('AppBundle:Users')->createQueryBuilder('a')->getQuery()->iterate();
		$handle = fopen('php://memory', 'r+');

		while (false !== ($row = $iterableResult->next())) {
			fputcsv($handle, array(
				$row[0]->getNom(),
				$row[0]->getPrenom(),
				$row[0]->getEmail(),
				$row[0]->getBirthday()->format('d/m/Y'),
				$row[0]->getGender(),
				$row[0]->getIdFacebook()
			));
			$em->detach($row[0]);
		}

		rewind($handle);
		$content = stream_get_contents($handle);
		fclose($handle);

		return new Response($content, 200, array(
			'Content-Type' => 'application/force-download',
			'Content-Disposition' => 'attachment; filename="exportUser.csv"'
		));
	}

	/**
	 * @Route("/deleteUser/{id}", name="deleteUser")
	 */
	public function deleteUserAction($id)
	{
		$user = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users')->find($id);

		$em = $this->getDoctrine()->getManager();
		$em->remove($user);
		$em->flush();

		return $this->redirect($this->generateUrl('user_datas'));
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
			'nbQuestion' => count($quizz->getQuestions()) +1,
			'createQuizz' => true
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
	 * @Route("/allQuizz", name="all_quizz")
	 */
	public function displayAllQuizzAction()
	{
		$quizzs = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->findBy(array("active" => 1),array('dateStart'=>'ASC'));

		return $this->render('back/displayAllQuizz.html.twig',array(
			'quizzs' => $quizzs
		));
	}

	/**
	 * @Route("/quizzRank/{idQuizz}", name="quizz_rank")
	 */
	public function displayQuizzRankingAction($idQuizz)
	{

	}
}