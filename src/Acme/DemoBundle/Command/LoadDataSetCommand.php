<?php

namespace Acme\DemoBundle\Command;

use Acme\DemoBundle\Entity\Movie;
use Acme\DemoBundle\Entity\Rate;
use BCC\Myrrix\MyrrixService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LoadDataSetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('acme:demo:load-data-set')
            ->setDescription('Load the demo data set')
            ->addArgument('directory', InputArgument::OPTIONAL, 'The directory where to load the data', __DIR__.'/../Resources/data/')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $entityManager EntityManager */
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var $myrrix MyrrixService */
        $myrrix = $this->getContainer()->get('bcc_myrrix.service');

        $output->writeln('Purging the database.');
        $entityManager->createQuery('DELETE FROM Acme\DemoBundle\Entity\Rate')->execute();
        $entityManager->createQuery('DELETE FROM Acme\DemoBundle\Entity\Movie')->execute();

        $output->writeln('Starting loading movies.');
        $movieHandle = fopen($input->getArgument('directory').'/movies.dat', 'r');
        $movieCounter = 0;
        while (($buffer = fgets($movieHandle)) !== false) {
            list($id, $title, $genres) = explode('::', $buffer);
            $movie = new Movie();
            $movie->setId($id);
            $movie->setTitle($title);
            $movie->setGenres(explode('|',$genres));

            foreach (explode('|',$genres) as $genre) {
                $myrrix->setItemTag((int)$id, trim($genre), (int)1);
            }

            $entityManager->persist($movie);
            if ((++$movieCounter)%1000 == 0) {
                $output->writeln(sprintf('Loading %d movies...', $movieCounter));
                $entityManager->flush();
                $entityManager->clear();
            }
        }
        fclose($movieHandle);
        $entityManager->flush();
        $entityManager->clear();
        $output->writeln(sprintf('Finish loading %d movies.', $movieCounter));

        $output->writeln('Starting loading rates.');
        $rateHandle = fopen($input->getArgument('directory').'/ratings.dat', 'r');
        $rateCount = 0;
        while (($buffer = fgets($rateHandle)) !== false) {
            list($userId, $movieId, $rating) = explode('::', $buffer);
            $rate = new Rate();
            $rate->setMovie($entityManager->getReference('Acme\DemoBundle\Entity\Movie', $movieId));
            $rate->setUserId($userId);
            $rate->setRating($rating*2);

            $myrrix->setPreference((int)$userId, (int)$movieId, (float)$rating*2);

            $entityManager->persist($rate);
            if ((++$rateCount)%1000 == 0) {
                $output->writeln(sprintf('Loading %d rates...', $rateCount));
                $entityManager->flush();
                $entityManager->clear();
            }
        }
        fclose($rateHandle);
        $entityManager->flush();
        $entityManager->clear();
        $output->writeln(sprintf('Finish loading %d rates.', $rateCount));

        $output->writeln('Refreshing myrrix model.');
        $myrrix->refresh();

        return 0;
    }
}
