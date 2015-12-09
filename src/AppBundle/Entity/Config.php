<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="config")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ConfigRepository")
 */
class Config
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
     * @ORM\Column(name="themeColor", type="text")
     */
    private $themeColor;

    /**
     * @var string
     *
     * @ORM\Column(name="imagePath", type="text")
     */
    private $imagePath;

    /**
     * @var string
     *
     * @ORM\Column(name="videoPath", type="text")
     */
    private $videoPath;


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
     * Set themeColor
     *
     * @param string $themeColor
     * @return Config
     */
    public function setThemeColor($themeColor)
    {
        $this->themeColor = $themeColor;

        return $this;
    }

    /**
     * Get themeColor
     *
     * @return string 
     */
    public function getThemeColor()
    {
        return $this->themeColor;
    }

    /**
     * Set imagePath
     *
     * @param string $imagePath
     * @return Config
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    /**
     * Get imagePath
     *
     * @return string 
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * Set videoPath
     *
     * @param string $videoPath
     * @return Config
     */
    public function setVideoPath($videoPath)
    {
        $this->videoPath = $videoPath;

        return $this;
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
}
