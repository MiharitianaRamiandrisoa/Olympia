<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\RestaurantPlat;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

final class RestaurantPlatCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = RestaurantPlat::class;

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                ImageField::new('imageMedia', 'Photo')
                    ->setBasePath('')
                    ->formatValue(static fn (?Media $media): ?string => $media?->getChemin()),
                AssociationField::new('restaurant', 'Restaurant'),
                TextField::new('nom', 'Plat'),
                NumberField::new('prix', 'Prix (Ar)')->setNumDecimals(0),
                IntegerField::new('ordre', 'Ordre'),
                BooleanField::new('estActif', 'Publié')->renderAsSwitch(),
            ];
        }

        return [
            AssociationField::new('restaurant', 'Restaurant')->setRequired(true),
            TextField::new('nom', 'Nom du plat')->setRequired(true),
            TextareaField::new('description', 'Description')->setRequired(false),
            NumberField::new('prix', 'Prix (Ar)')
                ->setNumDecimals(0)
                ->setHelp('Laissez vide si le prix varie ou n’est pas communiqué.'),
            Field::new('imageUpload', 'Photo du plat')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
                ->setFormTypeOption('constraints', [new File(
                    maxSize: '8M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/svg+xml'],
                    mimeTypesMessage: 'Veuillez sélectionner une image JPEG, PNG, WebP, AVIF ou SVG.',
                )])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif,image/svg+xml'])
                ->setHelp('Une photo déjà liée ? Laissez vide pour la conserver.'),
            IntegerField::new('ordre', 'Ordre d’affichage')->setHelp('0 apparaît en premier.'),
            BooleanField::new('estActif', 'Afficher sur le site')->renderAsSwitch(),
        ];
    }
}
