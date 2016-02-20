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
            ->add('description','textarea',array('label'=>'Énoncé de la question','required' => true))
            ->add('imgPath','file',array('label'=>'Image illustrant la question','required'=>false,"data_class"=>null))
            ->add('videoPath','text',array('label'=>"Vidéo illustrant la question (url)",'required' => false))
	        ->add('quizz','entity',array(
		        'label' =>false,
		        'property' => ('id'),
                "class" => "AppBundle:Quizz",
	            'attr' => array('class'=>'hidden')
	        ))
	        ->add('response1','text',array('required'=>true,'label'=>'Réponse 1','mapped' => false))
	        ->add('correct1','checkbox',array('required'=>false,'label'=>'Réponse correcte ','mapped' => false))
	        ->add('response2','text',array('required'=>true,'label'=>'Réponse 2','mapped' => false))
	        ->add('correct2','checkbox',array('required'=>false,'label'=>'Réponse correcte ','mapped' => false))
	        ->add('response3','text',array('required'=>false,'label'=>'Réponse 3','mapped' => false))
	        ->add('correct3','checkbox',array('required'=>false,'label'=>'Réponse correcte ','mapped' => false))
	        ->add('response4','text',array('required'=>false,'label'=>'Réponse 4','mapped' => false))
	        ->add('correct4','checkbox',array('required'=>false,'label'=>'Réponse correcte ','mapped' => false))
	        ->add('Valider','submit',array("attr"=>array("class"=>"button")))
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
