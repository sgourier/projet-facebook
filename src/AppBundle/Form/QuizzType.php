<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class QuizzType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'label'=> 'Titre du quizz : ',
                'required' => true
            ))
            ->add('description', 'textarea', array(
                'label'=> 'Description du quizz : ',
                'required' => false
            ))
            ->add('giftText', 'textarea', array(
                'label'=> 'Description de la récompense : ',
                'required' => false
            ))
            ->add('giftImg', 'file', array(
                'label'=> 'Image de la récompense : ',
                'required' => false,
                'data_class' => null
            ))
            ->add('datetimeStart', 'text', array(
                'label'=> "Date d'ouverture : ",
                'required' => true,
                'attr'=>array(
                    'class' => 'datetimepicker'
                ),
                'mapped' => false
            ))
            ->add('datetimeEnd', 'text', array(
                'label'=> 'Date de fermeture : ',
                'required' => true,'attr'=>array(
                    'class' => 'datetimepicker'
                ),
                'mapped' => false
            ))
            ->add('Valider','submit',array("attr"=>array("class"=>"button")))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Quizz'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_quizz';
    }
}
