<?php

namespace App\Controller\Admin;

use App\Entity\BoutiquePhoto;
use App\Entity\Media;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

final class BoutiquePhotoCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = BoutiquePhoto::class;

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                ImageField::new('imageMedia', 'Photo')
                    ->setBasePath('')
                    ->formatValue(static fn (?Media $media): ?string => $media?->getChemin()),
                AssociationField::new('boutique', 'Boutique'),
                TextField::new('legende', 'Légende'),
                IntegerField::new('ordre', 'Ordre'),
                BooleanField::new('estActif', 'Publiée')->renderAsSwitch(),
            ];
        }

        return [
            AssociationField::new('boutique', 'Boutique')->setRequired(true),
            Field::new('imageUpload', 'Photo de la boutique')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
                ->setFormTypeOption('constraints', [new File(
                    maxSize: '8M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/svg+xml'],
                    mimeTypesMessage: 'Veuillez sélectionner une image JPEG, PNG, WebP, AVIF ou SVG.',
                )])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif,image/svg+xml'])
                ->setHelp('Ajoutez une photo de la boutique ou de son espace de vente.'),
            TextField::new('legende', 'Légende')->setRequired(false),
            TextareaField::new('description', 'Description de la photo')->setRequired(false)
                ->setHelp('Expliquez ce que montre la photo. Cette description sera visible sur le site.'),
            IntegerField::new('ordre', 'Ordre d’affichage')->setHelp('0 apparaît en premier.'),
            BooleanField::new('estActif', 'Afficher sur le site')->renderAsSwitch(),
        ];
    }
}
