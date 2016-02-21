<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Resultat
 *
 * @ORM\Table(name="resultat")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ResultatRepository")
 */
class Resultat
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="score", type="integer")
     */
    private $score;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timeStart", type="datetime")
     */
    private $timeStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="responseTime", type="datetime")
     */
    private $responseTime;

	/**
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Quizz")
	 * @ORM\JoinColumn(name="idQuizz", nullable=false)
	 */
	private $quizz;

	/**
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Users")
	 * @ORM\JoinColumn(name="idUser", nullable=false)
	 */
	private $user;

    /**
     * Resultat constructor.
     *
     */
    public function __construct()
    {
        $this->score        = 0;
        $this->timeStart    = new \DateTime();
        $this->responseTime = new \DateTime();
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set score
     *
     * @param integer $score
     * @return Resultat
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return integer 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set timeStart
     *
     * @param \DateTime $timeStart
     * @return Resultat
     */
    public function setTimeStart($timeStart)
    {
        $this->timeStart = $timeStart;

        return $this;
    }

    /**
     * Get timeStart
     *
     * @return \DateTime 
     */
    public function getTimeStart()
    {
        return $this->timeStart;
    }

    /**
     * Set responseTime
     *
     * @param \DateTime $responseTime
     * @return Resultat
     */
    public function setResponseTime($responseTime)
    {
        $this->responseTime = $responseTime;

        return $this;
    }

    /**
     * Get responseTime
     *
     * @return \DateTime 
     */
    public function getResponseTime()
    {
        return $this->responseTime;
    }

    /**
     * Set quizz
     *
     * @param \AppBundle\Entity\Quizz $quizz
     * @return Resultat
     */
    public function setQuizz(\AppBundle\Entity\Quizz $quizz)
    {
        $this->quizz = $quizz;

        return $this;
    }

    /**
     * Get quizz
     *
     * @return \AppBundle\Entity\Quizz 
     */
    public function getQuizz()
    {
        return $this->quizz;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\Users $user
     * @return Resultat
     */
    public function setUser(\AppBundle\Entity\Users $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\Users
     */
    public function getUser()
    {
        return $this->user;
    }
}
