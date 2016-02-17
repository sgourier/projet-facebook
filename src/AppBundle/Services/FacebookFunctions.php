<?php
/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 09/12/2015
 * Time: 11:59
 */

namespace AppBundle\Services;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Facebook;
use AppBundle\Entity\Users;

//require_once __DIR__ . '/../facebook-php-sdk/src/Facebook/autoload.php';

class FacebookFunctions
{
	private $em;
	private $appId;
	private $appSecret;

	function __construct(Registry $doctrine,$appId,$appSecret)
	{
		$this->em = $doctrine->getManager();
		$this->appId = $appId;
		$this->appSecret = $appSecret;
	}

	function fbLogger()
	{
		return new Facebook\Facebook([
			'app_id' => $this->appId,
			'app_secret' => $this->appSecret,
			'default_graph_version' => 'v2.5',
		]);
	}

	function getFBUser($token)
	{
		$fb = $this->fbLogger();
		$requestUserName = $fb->get('/me?fields=gender,email,name,birthday,last_name,first_name',$token);
		return $requestUserName->getGraphUser();
	}

	function saveUser($token)
	{
		$repository = $this->em->getRepository('AppBundle:Users');
		$fbUser = $this->getFBUser($token);
		$user = $repository->findOneByIdFacebook($fbUser->getId());
		if(!is_object($user))
		{
			$newUser = new Users;
			$newUser->setIdFacebook($fbUser->getId());
			$newUser->setBirthday($fbUser->getBirthday());
			$newUser->setEmail($fbUser->getEmail());
			$newUser->setGender($fbUser->getGender());
			$newUser->setNom($fbUser->getLastName());
			$newUser->setPrenom($fbUser->getFirstName());
			$newUser->setToken($token);

			$this->em->persist($newUser);
			$this->em->flush();
		}
		else
		{
			$user->setToken($token);
			$this->em->persist($user);
			$this->em->flush();
		}
	}

	function sendNotification($idUser,$template,$ref,$href)
	{
		$fbLogger = $this->fbLogger();

		$fbLogger->get('/me/notifications?template='.$template.'&amp;href='.$href.'&amp;ref='.$ref);
	}

	function isAdmin()
	{
		$fb = $this->fbLogger();

	}
}