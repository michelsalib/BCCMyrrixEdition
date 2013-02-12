<?php

namespace Acme\DemoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RecommenderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('movies', 'collection', [
            'type' => 'integer',
            'options' => [
                'mapped'   => false,
                'required' => false,
            ],
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['movies']->children as $movieForm) {
            $movieForm->attr['movie'] = $form->getData()['movies'][$movieForm->getName()];
        }
    }

    public function getName()
    {
        return 'recommender';
    }
}
