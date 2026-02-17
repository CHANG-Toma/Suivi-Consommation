<?php

namespace App\Form;

use App\Entity\Alerte;
use App\Entity\Logement;
use App\Entity\TypeEnergie;
use App\Entity\User;
use App\Repository\LogementRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AlerteType extends AbstractType
{
    public function __construct(private readonly LogementRepository $logementRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];

        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Ex: Seuil gaz dépassé',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner un titre.',
                    ]),
                    new Length(max: 255, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Détail de l alerte (optionnel).',
                ],
            ])
            ->add('logement', EntityType::class, [
                'class' => Logement::class,
                'choice_label' => 'nom',
                'label' => 'Logement',
                'placeholder' => 'Sélectionner un logement',
                'query_builder' => fn () => $this->logementRepository
                    ->createQueryBuilder('l')
                    ->andWhere('l.user = :user')
                    ->setParameter('user', $user)
                    ->orderBy('l.nom', 'ASC'),
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un logement.',
                    ]),
                ],
            ])
            ->add('typeEnergie', EntityType::class, [
                'class' => TypeEnergie::class,
                'choice_label' => fn (TypeEnergie $typeEnergie) => sprintf(
                    '%s (%s)',
                    $typeEnergie->getNom() ?? '',
                    $typeEnergie->getUnite() ?? 'kWh'
                ),
                'label' => 'Type d\'énergie',
                'required' => false,
                'placeholder' => 'Tous types (optionnel)',
            ])
            ->add('seuil', NumberType::class, [
                'label' => 'Seuil',
                'required' => false,
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => 'Ex: 200',
                ],
                'help' => 'Valeur seuil optionnelle pour contextualiser l\'alerte.',
            ])
            ->add('lu', CheckboxType::class, [
                'label' => 'Alerte lue',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Alerte::class,
            'user' => null,
        ]);

        $resolver->setAllowedTypes('user', ['null', User::class]);
    }
}
