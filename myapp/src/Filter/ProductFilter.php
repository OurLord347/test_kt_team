<?php

namespace App\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use App\Entity\Category;
use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Lexik\Bundle\FormFilterBundle\Event\Subscriber;

class ProductFilter extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');
        $builder
            ->add('category_id', Filters\EntityFilterType::class, [
                'data_class' => Category::class,
                'class' => Category::class
            ])
            ->add('name', Filters\TextFilterType::class)
            ->add('description', Filters\TextFilterType::class)
            ->add('weight', Filters\TextFilterType::class)
            ->add('save', SubmitType::class, ['label' => 'Поиск']);

    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'validation_groups' => array('filtering') // avoid NotBlank() constraint-related message
        ));
    }
}