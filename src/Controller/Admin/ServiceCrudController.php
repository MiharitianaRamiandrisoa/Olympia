<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\Service;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;

final class ServiceCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Service::class;

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                ImageField::new('photoMedia', 'Image')
                    ->setBasePath('')
                    ->formatValue(static fn (?Media $media): ?string => $media?->getChemin()),
                TextField::new('nom', 'Nom'),
                TextField::new('telephone', 'Téléphone'),
                BooleanField::new('estMisEnAvant', 'Mis en avant')->renderAsSwitch(),
                BooleanField::new('estActif', 'Actif')->renderAsSwitch(),
            ];
        }

        return array_merge(iterator_to_array(parent::configureFields($pageName)), [
            Field::new('imageUpload', 'Image du service')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
                ->setFormTypeOption('constraints', [new Image(maxSize: '8M', maxWidth: 8000, maxHeight: 8000)])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif'])
                ->setHelp('Laissez vide lors de la modification pour conserver l’image actuelle.')
                ->onlyOnForms(),
        ]);
    }
}
