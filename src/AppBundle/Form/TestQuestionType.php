<?php

namespace AppBundle\Form;

use AppBundle\Entity\Question;
use AppBundle\Service\TestControl;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestQuestionType extends AbstractType
{
    private $testControl;

    /** @var TestControl $testControl */
    public function __construct($testControl)
    {
        $this->testControl = $testControl;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Question $question */
        $question       = $options['data']['question'];
        $answered       = $options['data']['answered'];
        $checkedAnswers = $this->testControl->prepareSelectedOptions($answered, $question);

        $builder->add('answers', EntityType::class, [
            'class' => 'AppBundle\Entity\Answer',
            'expanded' => true,
            'multiple' => $question->getCheckboxAnswers(),
            'label' => $question->getContent(),
            'required' => false,
            'choices' => $question->getAnswers(),
            'placeholder' => false,
            'data' => $checkedAnswers])
        ->add('next', SubmitType::class)
        ->add('previous', SubmitType::class)
        ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getName()
    {
        return 'app_bundle_test_question_type';
    }
}
