<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\Exposition;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;

final class ExpositionCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Exposition::class;

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [ImageField::new('imageMedia', 'Image')->setBasePath('')->formatValue(static fn (?Media $media): ?string => $media?->getChemin())];
        }

        return array_merge(iterator_to_array(parent::configureFields($pageName)), [
            Field::new('imageUpload', 'Affiche de l’exposition')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
                ->setFormTypeOption('constraints', [new Image(maxSize: '8M', maxWidth: 8000, maxHeight: 8000)])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif'])
                ->onlyOnForms(),
        ]);
    }
}
