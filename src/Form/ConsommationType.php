<?php

namespace App\Form;

use App\Entity\Consommation;
use App\Entity\Logement;
use App\Entity\TypeEnergie;
use App\Entity\User;
use App\Repository\LogementRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConsommationType extends AbstractType
{
    public function __construct(private readonly LogementRepository $logementRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];

        $builder
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
                'placeholder' => 'Sélectionner un type d\'énergie',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un type d\'énergie.',
                    ]),
                ],
            ])
            ->add('valeur', NumberType::class, [
                'label' => 'Valeur relevée',
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => 'Ex: 145.32',
                ],
                'help' => 'Saisissez une valeur positive.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner une valeur.',
                    ]),
                    new GreaterThanOrEqual(0),
                ],
            ])
            ->add('dateReleve', DateType::class, [
                'label' => 'Date du relevé',
                'widget' => 'single_text',
                'input' => 'datetime',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner la date du relevé.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consommation::class,
            'user' => null,
        ]);

        $resolver->setAllowedTypes('user', ['null', User::class]);
    }
}
