<?php
/**
 * Created by PhpStorm.
 * User: Sylvain Gourier
 * Date: 09/12/2015
 * Time: 11:59
 */

namespace AppBundle\Services;
use Facebook;

//require_once __DIR__ . '/../facebook-php-sdk/src/Facebook/autoload.php';

class FacebookFunctions
{
	function __construct()
	{

	}

	function fbLogger()
	{
		return new Facebook\Facebook([
			'app_id' => '1117392764937981',
			'app_secret' => '071b3100ebaa4b3d6147474399b2840f',
			'default_graph_version' => 'v2.5',
		]);
	}

	function getFBUser($token)
	{
		$fb = $this->fbLogger();
		$requestUserName = $fb->get('/me?fields=gender,email,birthday,name,first_name',$token);
		return $requestUserName->getGraphUser();
	}
}