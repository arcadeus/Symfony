<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => ' ',
                'constraints' => new NotBlank(),
                'required' => false
              ])
            ->add('service', ChoiceType::class, [
                'label' => ' ',
                'choices' => $options['services'],
                'choice_value' => 'id',
                'choice_label' => 'name',
                'choice_attr' => function ($service): array {
                    return ['data-price' => $service->getPrice()];
                },
                'attr' => ['onchange' => 'change_service()'],
              ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'services' => null
        ]);
    }
}
