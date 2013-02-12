<?php

namespace Acme\DemoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Rate
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Movie", inversedBy="rates")
     *
     * @var Movie
     */
    protected $movie;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $rating;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Movie $movie
     */
    public function setMovie(Movie $movie)
    {
        $this->movie = $movie;
    }

    /**
     * @return Movie
     */
    public function getMovie()
    {
        return $this->movie;
    }

    /**
     * @param int $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
