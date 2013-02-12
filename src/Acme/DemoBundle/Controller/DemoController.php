<?php

namespace Acme\DemoBundle\Controller;

use Acme\DemoBundle\Entity\Movie;
use Acme\DemoBundle\Form\Type\RecommenderType;
use BCC\Myrrix\MyrrixService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DemoController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        return [
        ];
    }

    /**
     * @Route("/movies")
     * @Template()
     */
    public function moviesAction()
    {
        return [
            'movies' => $this->getDoctrine()->getManager()->getRepository('Acme\DemoBundle\Entity\Movie')->findAll(),
        ];
    }

    /**
     * @Route("/movies/{id}")
     * @Template()
     */
    public function movieAction(Movie $movie)
    {
        /** @var $myrrix MyrrixService */
        $myrrix = $this->get('bcc_myrrix.service');

        $ids = $myrrix->getSimilarItems([$movie->getId()]);

        $ids = array_map(function ($result) {
            return $result[0];
        }, $ids);

        return [
            'movie' => $movie,
            'movies' => $this->getMoviesByIds($ids),
        ];
    }

    /**
     * @Route("/users")
     * @Template()
     */
    public function usersAction()
    {
        return [
            'users' => $this->getDoctrine()->getManager()->getRepository('Acme\DemoBundle\Entity\Rate')->createQueryBuilder('u')->groupBy('u.userId')->select('u.userId')->getQuery()->getResult(),
        ];
    }

    /**
     * @Route("/users/{id}")
     * @Template()
     */
    public function userAction($id)
    {
        /** @var $myrrix MyrrixService */
        $myrrix = $this->get('bcc_myrrix.service');

        $ids = $myrrix->getRecommendation((int)$id);

        $ids = array_map(function ($result) {
            return $result[0];
        }, $ids);

        return [
            'id' => $id,
            'rates' => $this->getDoctrine()->getManager()->getRepository('Acme\DemoBundle\Entity\Rate')->findBy(['userId' => $id]),
            'movies' => $this->getMoviesByIds($ids),
        ];
    }

    /**
     * @Route("/recommend")
     * @Template()
     */
    public function recommendAction()
    {
        /** @var $session Session */
        $session = $this->get('session');

        $currentRates = $session->get('currentRates', []);
        $currentRatedMovies = $this->getMoviesByIds(array_keys($currentRates));

        /** @var $movies Movie[] */
        $movies = $session->get('movies');

        // if no movie is previously rated, simply rand the database
        if (!$movies && !$currentRates) {
            $movies = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository('Acme\DemoBundle\Entity\Movie')
                ->findAll();
            shuffle($movies);
            $movies = array_slice($movies, 0, 10);
        }
        // if there is previously rate movie, get recommendation from myrrix
        else if (!$movies && $currentRates) {
            /** @var $myrrix MyrrixService */
            $myrrix = $this->get('bcc_myrrix.service');

            $ids = $myrrix->getRecommendationToAnonymous($currentRates);

            $ids = array_map(function ($result) {
                return $result[0];
            }, $ids);

            $movies = $this->getMoviesByIds($ids);
        }

        // reset session, create form
        $session->set('movies', $movies);
        $form = $this->createForm(new RecommenderType(), ['movies' => $movies]);

        // handle form submission
        if (!$this->getRequest()->isMethodSafe() && $form->bind($this->getRequest())->isValid())
        {
            // save every rated movie
            foreach ($form->get('movies')->all() as $movieForm) {
                if (null !== $rate = $movieForm->getData()) {
                    $currentRates[(int)$movies[$movieForm->getConfig()->getName()]->getId()] = (float)$rate;
                }
            }

            // save rates and reset movie session
            $session->set('currentRates', $currentRates);
            $session->remove('movies');

            // redirect to recommendation
            return $this->redirect($this->generateUrl('acme_demo_demo_recommend'));
        }

        return [
            'movies' => $movies,
            'form'   => $form->createView(),
            'currentRates' => $currentRates,
            'currentRatedMovies' => $currentRatedMovies,
        ];
    }

    /**
     * @Route("/recommend/reset")
     * @Template()
     */
    public function resetAction()
    {
        /** @var $session Session */
        $session = $this->get('session');

        $session->remove('movies');
        $session->remove('currentRates');

        return $this->redirect($this->generateUrl('acme_demo_demo_recommend'));
    }

    /**
     * @Route("/recommend/more")
     * @Template()
     */
    public function moreAction()
    {
        /** @var $session Session */
        $session = $this->get('session');

        $session->remove('movies');

        return $this->redirect($this->generateUrl('acme_demo_demo_recommend'));
    }

    /**
     * @param array $ids
     * @return Movie[]
     */
    protected function getMoviesByIds(array $ids)
    {
        if (!$ids) {
            return [];
        }

        return $this->getDoctrine()->getManager()->getRepository('Acme\DemoBundle\Entity\Movie')->findBy(['id' => $ids]);
    }
}
