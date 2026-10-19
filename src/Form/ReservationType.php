<?php

namespace App\Form;

use App\Dto\ReservationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom complet', 'attr' => ['autocomplete' => 'name']])
            ->add('email', EmailType::class, ['label' => 'Adresse e-mail', 'attr' => ['autocomplete' => 'email']])
            ->add('telephone', TelType::class, ['label' => 'Telephone (facultatif)', 'required' => false, 'attr' => ['autocomplete' => 'tel']])
            ->add('nombrePlaces', IntegerType::class, ['label' => 'Nombre de places', 'attr' => ['min' => 1, 'max' => 20]])
            ->add('submit', SubmitType::class, ['label' => 'Confirmer ma reservation']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ReservationRequest::class, 'csrf_token_id' => 'reservation']);
    }
}
