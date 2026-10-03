<?php

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;

final class PromotionTypeFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName = 'type', string $label = 'Type'): self
    {
        return (new self())
            ->setFilterFqcn(self::class)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(ChoiceFilterType::class)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle')
            ->setFormTypeOption('value_type_options.choices', [
                'Boutiques' => 'boutique',
                'Restaurants' => 'restaurant',
            ]);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $value = $filterDataDto->getValue();
        if (!is_string($value) || !in_array($value, ['boutique', 'restaurant'], true)) {
            return;
        }

        $alias = $filterDataDto->getEntityAlias();
        $entityClass = $value === 'boutique' ? 'App\\Entity\\Boutique' : 'App\\Entity\\Restaurant';
        $childAlias = $value === 'boutique' ? 'boutique' : 'restaurant';

        $queryBuilder->andWhere(sprintf(
            'EXISTS (SELECT %1$s.id FROM %2$s %1$s WHERE %1$s.enseigne = %3$s.enseigne)',
            $childAlias,
            $entityClass,
            $alias,
        ));
    }
}
