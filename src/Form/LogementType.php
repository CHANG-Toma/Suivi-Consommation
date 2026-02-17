<?php

namespace App\Form;

use App\Entity\Logement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LogementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du logement',
                'help' => 'Ex: Appartement principal, Maison secondaire...',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner un nom pour ce logement.',
                    ]),
                    new Length(max: 255, maxMessage: 'Le nom ne peut pas depasser {{ limit }} caracteres.'),
                ],
                'attr' => [
                    'placeholder' => 'Appartement principal',
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner une adresse.',
                    ]),
                    new Length(max: 255, maxMessage: 'L adresse ne peut pas depasser {{ limit }} caracteres.'),
                ],
                'attr' => [
                    'placeholder' => '12 Rue des Erables, 75000 Paris',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Logement::class,
        ]);
    }
}
