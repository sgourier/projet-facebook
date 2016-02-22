<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Question
 *
 * @ORM\Table(name="question")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\QuestionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Question
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
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string $imgPath
     * @Assert\Image( maxSize = "4096k", mimeTypesMessage = "Merci de fournir une image valide")
     * @ORM\Column(name="imgPath", type="text", nullable=true)
     */
    private $imgPath;

    /**
     * @var string
     *
     * @ORM\Column(name="videoPath", type="text", nullable=true)
     */
    private $videoPath;

	/**
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Quizz")
	 * @ORM\JoinColumn(name="idQuizz", nullable=false)
	 */
	private $quizz;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Reponse", mappedBy="question", cascade={"persist"})
     */
    private $reponses;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateAdd", type="datetime")
     */
    private $dateAdd;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reponses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dateAdd = new \DateTime();
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
     * Set description
     *
     * @param string $description
     * @return Question
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set imgPath
     *
     * @param string $imgPath
     * @return Question
     */
    public function setImgPath($imgPath)
    {
        $this->imgPath = $imgPath;

        return $this;
    }

    /**
     * Get imgPath
     *
     * @return string 
     */
    public function getImgPath()
    {
        return $this->imgPath;
    }

    /**
     * Set videoPath
     *
     * @param string $videoPath
     * @return Question
     */
    public function setVideoPath($videoPath)
    {
        $this->videoPath = $videoPath;

        return $this;
    }

    public function getFullImagePath()
    {
        return null === $this->imgPath ? null : $this->getUploadRootDir() . $this->imgPath;
    }

    public function getWebPath()
    {
        return null === $this->imgPath ? null : $this->getUploadDir().'/'.$this->imgPath;
    }

    protected function getUploadDir()
    {
        return 'upload/question/'.$this->getId();
    }

    protected function getUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return $this->getTmpUploadRootDir().$this->getId()."/";
    }

    protected function getTmpUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return realpath('./') . '/upload/question/';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function uploadImage() {
        // the file property can be empty if the field is not required
        $img = $this->imgPath;

        if ( null !== $img && $img instanceof UploadedFile && file_exists( $img->getPathname() ) && $img->getPath() != $this->getUploadRootDir())
        {
            if ( ! $this->id )
            {
                $img->move( $this->getTmpUploadRootDir(), $img->getFilename() . "." . $img->getClientOriginalExtension() );
            }
            else
            {
                if ( ! is_dir( $this->getUploadRootDir() ) )
                {
                    mkdir( $this->getUploadRootDir() );
                }
                $img->move( $this->getUploadRootDir(), $img->getFilename() . "." . $img->getClientOriginalExtension() );
            }
            $this->setImgPath( $img->getFilename() . "." . $img->getClientOriginalExtension() );
        }

    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function moveImage()
    {
        if ( null !== $this->imgPath && file_exists( $this->getTmpUploadRootDir() . $this->imgPath))
        {
            if ( ! is_dir( $this->getUploadRootDir() ) )
            {
                mkdir( $this->getUploadRootDir() );
            }

            copy( $this->getTmpUploadRootDir() . $this->imgPath, $this->getFullImagePath() );
            unlink( $this->getTmpUploadRootDir() . $this->imgPath );
        }
    }

    /**
     * @ORM\PreRemove()
     */
    public function removeImage()
    {
        if(file_exists($this->getFullImagePath()))
            unlink($this->getFullImagePath());
        if(is_dir($this->getUploadRootDir()))
        {
            foreach (scandir($this->getUploadRootDir()) as $item) {
                if($item != '.' && $item != '..')
                    unlink($this->getUploadRootDir().$item);
            }

            rmdir($this->getUploadRootDir());
        }
    }

    /**
     * Get videoPath
     *
     * @return string 
     */
    public function getVideoPath()
    {
        return $this->videoPath;
    }

    /**
     * Set quizz
     *
     * @param \AppBundle\Entity\Quizz $quizz
     * @return Question
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
     * Add reponses
     *
     * @param \AppBundle\Entity\Reponse $reponses
     * @return Question
     */
    public function addReponse(\AppBundle\Entity\Reponse $reponses)
    {
        $this->reponses[] = $reponses;

        return $this;
    }

    /**
     * Remove reponses
     *
     * @param \AppBundle\Entity\Reponse $reponses
     */
    public function removeReponse(\AppBundle\Entity\Reponse $reponses)
    {
        $this->reponses->removeElement($reponses);
    }

    /**
     * Get reponses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReponses()
    {
        return $this->reponses;
    }

    /**
     * Set dateAdd
     *
     * @param \DateTime $dateAdd
     * @return Question
     */
    public function setDateAdd($dateAdd)
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    /**
     * Get dateAdd
     *
     * @return \DateTime 
     */
    public function getDateAdd()
    {
        return $this->dateAdd;
    }
}
