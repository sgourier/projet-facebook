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

class BackController extends Controller
{
	/**
	 * Redirige vers la page d'autorisation facebook
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
	 * Réceptionne les données d'autorisation FaceBook, enregistre l'utilisateur s'il n'existe pas en BDD
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
	 * Permet d'afficher le meu du back office sur la gauche
	 * @Route("/backMenu", name="back_menu")
	 *
	 * @return object
	 */
	public function backMenuAction()
	{
		return $this->render('back/backMenu.html.twig');
	}

	/**
	 * @Route("/newQuizz", name="new_quizz")
	 * @return object
	 */
	public function newQuizzAction()
	{
		$quizz = new Quizz();

		$quizzForm = $this->createForm(new QuizzType(),$quizz,array('method'=>'POST', 'action' => $this->generateUrl('save_quizz')));

		return $this->render('back/quizzForm.html.twig',array(
			'form' => $quizzForm->createView(),
		    'createQuizz' => true
		));
	}

	/**
	 * @Route("/saveQuizz/{idQuizz}", name="save_quizz", defaults={"idQuizz" = null})
	 * @Method({"POST"})
	 */
	public  function  saveQuizzAction($idQuizz = null)
	{
		$quizz = new Quizz;
		$quizzImg = null;

		if($idQuizz != null)
		{
			$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($idQuizz);
			$quizzImg = $quizz->getGiftImg();
		}

		$formQuizz = $this->createForm(new QuizzType(),$quizz);

		$request = $this->get('request');
		$ajax = $request->get('ajax',null);

		$formQuizz->handleRequest( $request );
		if ( $formQuizz->isValid() )
		{
			$repoQuizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz');
			$dateStart = new \DateTime($formQuizz['datetimeStart']->getData());
			$dateEnd = new \DateTime($formQuizz['datetimeEnd']->getData());

			if($quizz->getGiftImg() == null && $request->get('deleteImg',null) == 0)
			{
				$quizz->setGiftImg($quizzImg);
			}

			if($repoQuizz->verifyDates($dateStart,$dateEnd,$idQuizz) == 0)
			{
				$quizz->setDateStart($dateStart);
				$quizz->setDateEnd($dateEnd);
				$this->getDoctrine()->getManager()->persist($quizz);
				$this->getDoctrine()->getManager()->flush();
			}
			else if($ajax == null)
			{
				return $this->render('back/quizzForm.html.twig',array(
					'form' => $formQuizz->createView(),
					'error' => true,
					'createQuizz' => true
				));
			}
			else
			{
				return new Response('error');
			}
		}
		else if($ajax == null)
		{
			return $this->render('back/quizzForm.html.twig',array(
				'form' => $formQuizz->createView(),
				'error' => true,
				'createQuizz' => true
			));
		}
		else
		{
			return new Response('error');
		}

		if($ajax == null)
		{
			return $this->redirect($this->generateUrl("quizz_details",array(
				'idQuizz' => $quizz->getId()
			)));
		}
		else
		{
			return new Response('ok');
		}
	}

	/**
	 * @Route("/userDatas/{offset}/{search}", name="user_datas")
	 */
	public function displayUserDatasAction($offset = 0,$search = null)
	{
		$limit = 50;
		if($this->get('request')->isMethod('POST'))
		{
			$search = $this->get('request')->get('searchName');
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
		$em = $this->getDoctrine()->getManager();

		$iterableResult = $em->getRepository('AppBundle:Users')->createQueryBuilder('a')->getQuery()->iterate();
		$handle = fopen('php://memory', 'r+');

		while (false !== ($row = $iterableResult->next())) {
			fputcsv($handle, array(
				$row[0]->getNom(),
				$row[0]->getPrenom(),
				$row[0]->getEmail(),
				$row[0]->getBirthday(),
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
	 * @Route("/newQuestion/{idQuizz}/{idQuestion}/{ajax}", name="new_question", defaults={"idQuestion" = null})
	 */
	public function newQuestionAction($idQuizz,$idQuestion = null,$ajax = null)
	{
		$question = new Question;
		$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($idQuizz);
		$question->setQuizz($quizz);

		if($idQuestion != null)
		{
			$question = $this->getDoctrine()->getManager()->getRepository('AppBundle:Question')->find($idQuestion);
		}
		$form = $this->createForm(new QuestionType(), $question,array('action' => $this->generateUrl('save_question',array('idQuestion'=>$idQuestion)),'attr'=>array('class'=>'modifQuestionForm')));

		$nbQuestion = $quizz->getQuestions()->count() +1;

		if($ajax != null)
		{
			if($question->getId() != null)
			{
				$form = $this->setFormResponsesValues($form,$question);
				$nbQuestion = $quizz->getQuestions()->indexOf($question)+1;
			}

			return $this->render(":back:shortQuestionForm.html.twig", array(
				"form" => $form->createView(),
				'nbQuestion' => $nbQuestion,
				'question' => $question
			));
		}
		else
		{
			return $this->render(":back:new_question.html.twig", array(
				"form" => $form->createView(),
				'nbQuestion' => $nbQuestion,
				'createQuizz' => true
			));
		}
	}

	private function setFormResponsesValues($form,$question)
	{
		$cptResponse = 1;

		foreach($question->getReponses() as $response)
		{
			$form->get('response'.$cptResponse)->setData($response->getDescription());
			$form->get('correct'.$cptResponse)->setData($response->getValid());

			$cptResponse++;
		}

		return $form;
	}

	/**
	 * @Route("/saveQuestion/{idQuestion}", name="save_question", defaults={"idQuestion" = null})
	 */
	public function saveQuestionAction($idQuestion = null)
	{
		$question = new Question();
		$modif = false;
		$imgPath = null;

		if($idQuestion != null)
		{
			$question = $this->getDoctrine()->getManager()->getRepository('AppBundle:Question')->find($idQuestion);
			$imgPath = $question->getImgPath();
			$modif = true;
		}

		$formQuestion = $this->createForm(new QuestionType(),$question);
		$request = $this->get( 'request' );

		$ajax = $request->get('ajax',null);

		$formQuestion->handleRequest( $request );
		if ( $formQuestion->isValid() )
		{
			if($question->getImgPath() == null && $request->get('deleteImg',null) == 0)
			{
				$question->setImgPath($imgPath);
			}
			$this->getDoctrine()->getManager()->persist($question);
			$this->getDoctrine()->getManager()->flush();
			$this->saveResponses($formQuestion,$question,$modif);
		}

		if($ajax == null)
		{
			return $this->redirect($this->generateUrl('new_question',array('idQuizz'=>$question->getQuizz()->getId())));
		}
		else if($modif == false)
		{
			return $this->redirect($this->generateUrl('new_question',array(
				'idQuizz'=> $question->getQuizz()->getId(),
				'idQuestion' => $question->getId(),
				'ajax' => 1
				)
			));
		}
		else
		{
			return new Response('ok');
		}
	}

	private function saveResponses($formQuestion,$question,$modif)
	{
		for($cptReponse = 1; $cptReponse < 5; $cptReponse++)
		{
			if($formQuestion['response'.$cptReponse]->getData() != null)
			{
				if($modif)
				{
					$response = $this->getDoctrine()->getManager()->getRepository('AppBundle:Reponse')->findOneBy(array('question'=>$question->getId()),array(),1,$cptReponse-1);
				}
				else
				{
					$response = new Reponse;
					$response->setQuestion($question);
				}
				$response->setDescription($formQuestion['response'.$cptReponse]->getData());
				$response->setValid($formQuestion['correct'.$cptReponse]->getData());
				$this->getDoctrine()->getManager()->persist($response);
			}
		}

		$this->getDoctrine()->getManager()->flush();
	}

	/**
	 * @Route("/revomeQuestion/{idQuizz}/{idQuestion}", name="remove_question")
	 */
	public function removeQuestionAction($idQuizz,$idQuestion)
	{
		$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($idQuizz);
		$question = $this->getDoctrine()->getManager()->getRepository('AppBundle:Question')->find($idQuestion);

		foreach($question->getReponses() as $response)
		{
			$this->getDoctrine()->getManager()->remove($response);
		}

		$quizz->removeQuestion($question);
		$this->getDoctrine()->getManager()->remove($question);
		$this->getDoctrine()->getManager()->persist($quizz);
		$this->getDoctrine()->getManager()->flush();

		return new Response('ok');
	}

	/**
	 * @Route("/removeQuizz/{idQuizz}", name="remove_quizz")
	 */
	public function removeQuizzAction($idQuizz)
	{
		$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($idQuizz);

		foreach($quizz->getQuestions() as $question)
		{
			foreach($question->getReponses() as $response)
			{
				$this->getDoctrine()->getManager()->remove($response);
			}

			$this->getDoctrine()->getManager()->remove($question);
		}

		$this->getDoctrine()->getManager()->remove($quizz);
		$this->getDoctrine()->getManager()->flush();

		return $this->redirect($this->generateUrl('all_quizz'));
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
	 * @Route("/quizzDetails/{idQuizz}", name="quizz_details")
	 */
	public function detailQuizzAction($idQuizz)
	{
		$quizz = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->find($idQuizz);

		$now = new \DateTime();

		$form = null;
		$isPassed = true;

		if($quizz->getDateStart() > $now)
		{
			$form = $this->createForm(new QuizzType(),$quizz,array('method'=>'post','action'=>$this->generateUrl('save_quizz',array('idQuizz'=>$idQuizz)),'attr'=>array('class'=>'modifQuizzForm')));
			$form->get('datetimeStart')->setData($quizz->getDateStart()->format('Y-m-d H:i'));
			$form->get('datetimeEnd')->setData($quizz->getDateEnd()->format('Y-m-d H:i'));
			$form = $form->createView();
			$isPassed = false;
		}

		return $this->render('back/detailQuizz.html.twig',array(
			'quizz' => $quizz,
			'formQuizz' => $form,
			'isPassed' => $isPassed
		));
	}

	/**
	 * @Route("/quizzRank/{idQuizz}", name="quizz_rank")
	 */
	public function displayQuizzRankingAction($idQuizz)
	{

	}

	/**
	 * @Route("/checkNewQuizzNotif", name="check_new_quizz_notif")
	 */
	public function checkNewQuizzNotifAction()
	{
		$template = "Un nouveau quizz est disponible, venez participer !";
		$ref = "newQuizz";
		$href = $this->generateUrl('homepage');

		$quizzList = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->findBy(array('active'=>1,'startNotified'=>0));
		$now = new \DateTime();
		$fbServ = $this->get('facebook_service');

		foreach($quizzList as $quizz)
		{
			if($quizz->getDateStart() < $now)
			{
				$users = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users')->findAll();
				foreach($users as $user)
				{
					$fbServ->sendNotification($user->getId(),$template,$ref,$href,$user->getToken());
				}

				$quizz->setStartNotified(true);
				$this->getDoctrine()->getManager()->persist($quizz);
			}
		}

		$this->getDoctrine()->getManager()->flush();
		return new Response('ok');
	}

	/**
	 * @Route("/checkCloseQuizzNotif", name="check_close_quizz_notif")
	 */
	public function checkCloseQuizzNotifAction()
	{
		$template = "Le quizz auquel vous avez participé est maintenant fini, venez consulter votre résultat !";
		$ref = "closedQuizz";

		$quizzList = $this->getDoctrine()->getManager()->getRepository('AppBundle:Quizz')->findBy(array('active'=>1,'endNotified'=>0));
		$now = new \DateTime();
		$fbServ = $this->get('facebook_service');

		foreach($quizzList as $quizz)
		{
			if($quizz->getDateEnd() < $now)
			{
				$href = $this->generateUrl('general_classement',array('idQuizz'=>$quizz->getId()));
				$users = $this->getDoctrine()->getManager()->getRepository('AppBundle:Users')->findAll();
				foreach($users as $user)
				{
					$results = $user->getResults();
					foreach($results as $result)
					{
						if($result->getQuizz()->getId() == $quizz->getId())
						{
							$fbServ->sendNotification($user->getId(),$template,$ref,$href,$user->getToken());
						}
					}
				}

				$quizz->setEndNotified(true);
				$this->getDoctrine()->getManager()->persist($quizz);
			}
		}

		$this->getDoctrine()->getManager()->flush();

		return new Response('ok');
	}
}