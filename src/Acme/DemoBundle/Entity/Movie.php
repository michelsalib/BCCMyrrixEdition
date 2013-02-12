<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Movie
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(nullable=false)
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\OneToMany(targetEntity="Rate", mappedBy="movie")
     *
     * @var Collection|Rate[]
     */
    protected $rates;

    /**
     * @ORM\Column(type="array")
     *
     * @var string[]
     */
    protected $genres;

    function __construct()
    {
        $this->rates = new ArrayCollection();
        $this->genres = [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param Rate $rate
     */
    public function addRate(Rate $rate)
    {
        $this->rates[] = $rate;
    }

    /**
     * @return Rate[]|Collection
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param array $genres
     */
    public function setGenres(array $genres)
    {
        $this->genres = $genres;
    }

    /**
     * @return string[]
     */
    public function getGenres()
    {
        return $this->genres;
    }
}
