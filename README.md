Myrrix Edition
========================

Welcome to the Myrrix demo for Symfony2. It relies on the Symfony2 standard
edition, adding BCCMyrrixBundle, and a tweaked version of the AcmeBundle that
runs an example of recommender engine based on the MovieLens dataset.

1) Whats inside
----------------------------------

- Symfony2 standard edition 2.2 RC1 (http://symfony.com/)
- Myrrix serving layer 0.9 (http://myrrix.com/)
- The MovieLens dataset 1M (http://www.grouplens.org/node/73)
- A AcmeDemoBundle working as a recommending website

3) Running the example
----------------------------------

You first need to have composer installed:

   curl -s http://getcomposer.org/installer | php

First get the code. You can download it or get it via composer:

    php composer.phar create-project bcc/myrrix-edition path/to/install

Start myrrix using this command line:

    java -jar myrrix.jar --localInputDir app/cache/myrrix --port 84

Then install the dependencies:

    php composer.phar install

You then need to setup your database (you can change any default configuration
in the app/config/parameters.yml file):

   php app/console doctrine:database:create
   php app/console doctrine:schema:create

Then you have to load all the data of the MovieLens dataset into your database
and into the myrrix recommender engine:

   php app/console --env=prod acme:demo:load-data-set

This will take a very long time, it will output a message every 1000 inserted
rows, there are 1 000 000 items to load.

You can now navigate into the root url and test the recommender engine.
