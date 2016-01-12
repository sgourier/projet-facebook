<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Quizz;
use AppBundle\Entity\Users;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
/**
 * @Route("/view")
 */
class TestController extends Controller
{

    /**
     * @Route("/index", name="view_homepage")
     */
    public function indexAction()
    {
        $quizz = new Quizz();
        $quizz->setTitle("title");
        $quizz->setDescription("description");
        $quizz->setgiftText("giftText");
        $quizz->setgiftImg("favicon.png");
        $quizz->setgiftImg("images/".$quizz->getGiftImg());

        $user = new Users();
        $user->setNom("Nom");
        $user->setPrenom("Prenom");

        //Classement du user
        $classement=108;

        //liste des 10 user et le user connecté
        $users=array($user,$user,$user,$user,$user,$user,$user,$user,$user,$user,$user);
        return $this->render('index.html.twig',
            array('quizz' => $quizz,
                'users' => $users,
                'classement' => $classement
            ));
    }
}
