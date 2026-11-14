<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\RestaurantPhoto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

final class RestaurantPhotoCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = RestaurantPhoto::class;

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                ImageField::new('imageMedia', 'Photo')
                    ->setBasePath('')
                    ->formatValue(static fn (?Media $media): ?string => $media?->getChemin()),
                AssociationField::new('restaurant', 'Restaurant'),
                TextField::new('legende', 'Légende'),
                IntegerField::new('ordre', 'Ordre'),
                BooleanField::new('estActif', 'Publiée')->renderAsSwitch(),
            ];
        }

        return [
            AssociationField::new('restaurant', 'Restaurant')->setRequired(true),
            Field::new('imageUpload', 'Photo du restaurant')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
                ->setFormTypeOption('constraints', [new File(
                    maxSize: '8M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/svg+xml'],
                    mimeTypesMessage: 'Veuillez sélectionner une image JPEG, PNG, WebP, AVIF ou SVG.',
                )])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif,image/svg+xml'])
                ->setHelp('Choisissez une photo de la salle, de la terrasse ou de l’ambiance du restaurant.'),
            TextField::new('legende', 'Légende')->setRequired(false),
            TextareaField::new('description', 'Description de la photo')->setRequired(false)
                ->setHelp('Expliquez ce que montre la photo. Cette description sera visible sur le site.'),
            IntegerField::new('ordre', 'Ordre d’affichage')->setHelp('0 apparaît en premier.'),
            BooleanField::new('estActif', 'Afficher sur le site')->renderAsSwitch(),
        ];
    }
}
