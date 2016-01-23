<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Entity\Quizz;

class QuestionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description','textarea',array('label'=>'Énoncé de la question'))
            ->add('imgPath','file',array('label'=>'Image illustrant la question'))
            ->add('videoPath','text',array('label'=>"Vidéo illustrant la question (url)"))
	        ->add('quizz','entity',array(
		        'label' =>false,
		        'property' => ('id'),
                "class" => "AppBundle:Quizz",
	            'attr' => array('class'=>'hidden')
	        ))
	        ->add('Valider','submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Question'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_question';
    }
}
