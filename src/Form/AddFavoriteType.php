<?php

namespace App\Form;

use App\Entity\Favorite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Url;

class AddFavoriteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url',UrlType::class,[
                'constraints'=>
                    new Url([
                        'message'=>'Please enter a valid URL.'
                    ])
            ])
            ->add('name')
            ->add('category',CategoryType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Favorite::class,
        ]);
    }
}
