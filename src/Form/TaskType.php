<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class TaskType extends AbstractType
{
    public function __construct(private Security $security, private UserRepository $userRepository){}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                "required" => false,
                "attr" => [
                    "placeholder" => "Insert a title"
                ]
            ])
            ->add('description', TextareaType::class, [
                "attr" => [
                    "placeholder" => "Insert a description"
                ]
            ])
            ->add('priority', ChoiceType::class, [
                "choices" => [
                    "Feature" => "feature",
                    "Bug fix"=> "bug",
                    "Urgent" => "urgent"
                ],
                "invalid_message" => "Hola boy stop being so smart !!"
            ])
            ->add('assigned_to', EntityType::class, [
                "class" => User::class,
                "placeholder" => "Choose a person",
                'choice_label' => function(User $user){
                    return sprintf("%s --  %s", $user->getName(), $user->getEmail());
                },
                "choices" => $this->userRepository->findByRole(),
                'invalid_message' => "You're a smart booy !!"
            ])
            ->add("Save", SubmitType::class, [
                "attr" => [
                    "class" => "btn btn-success"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
