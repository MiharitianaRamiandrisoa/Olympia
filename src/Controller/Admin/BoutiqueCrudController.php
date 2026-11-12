<?php

namespace App\Controller\Admin;

use App\Entity\Boutique;
use App\Entity\Media;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

final class BoutiqueCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Boutique::class;

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setSearchFields([
                'enseigne.nom',
                'enseigne.slug',
                'categorie.nom',
                'description',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                ImageField::new('photoMedia', 'Photo')->setBasePath('')->formatValue(static fn (?Media $media): ?string => $media?->getChemin()),
                AssociationField::new('enseigne', 'Nom de la boutique'),
                AssociationField::new('categorie', 'Catégorie'),
                TextareaField::new('description', 'Description'),
                BooleanField::new('estMisEnAvant', 'Mettre en avant')->renderAsSwitch(),
            ];
        }

        return array_merge([
            TextField::new('enseigneNom', 'Nom de l’enseigne')->onlyOnForms()->setRequired(true),
            TextareaField::new('enseigneDescription', 'Description de l’enseigne')->onlyOnForms(),
            AssociationField::new('categorie', 'Catégorie')
                ->renderAsNativeWidget()
                ->setFormTypeOption('required', false)
                ->setFormTypeOption('placeholder', 'Choisir une catégorie')
                ->setFormTypeOption('choice_label', 'nom')
                ->setQueryBuilder(static fn ($queryBuilder) => $queryBuilder->orderBy('entity.nom', 'ASC'))
                ->onlyOnForms(),
            Field::new('enseigneLogoUpload', 'Logo de l’enseigne')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', false)
                ->setFormTypeOption('constraints', [new File(
                    maxSize: '8M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/svg+xml'],
                    mimeTypesMessage: 'Veuillez sélectionner une image JPEG, PNG, WebP, AVIF ou SVG.',
                )])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif,image/svg+xml'])
                ->setHelp('Laissez vide pour conserver le logo actuel. Les logos SVG sont conservés ; les autres images sont converties en WebP.')
                ->onlyOnForms(),
            TextField::new('enseigneTelephone', 'Téléphone de l’enseigne')->onlyOnForms(),
            TextField::new('enseigneEmail', 'Email de l’enseigne')->onlyOnForms(),
            TextField::new('enseigneSiteWeb', 'Site web de l’enseigne')->onlyOnForms(),
            TextField::new('enseigneFacebook', 'Facebook de l’enseigne')->onlyOnForms(),
            TextField::new('enseigneInstagram', 'Instagram de l’enseigne')->onlyOnForms(),
            BooleanField::new('enseigneEstActif', 'Enseigne active')->onlyOnForms(),
        ], iterator_to_array(parent::configureFields($pageName)), [
            Field::new('photoUpload', 'Photo de la boutique')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
                ->setFormTypeOption('constraints', [new File(
                    maxSize: '8M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/svg+xml'],
                    mimeTypesMessage: 'Veuillez sélectionner une image JPEG, PNG, WebP, AVIF ou SVG.',
                )])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif,image/svg+xml'])
                ->setHelp('Une image déjà liée ? Laissez vide pour la conserver, ou choisissez une nouvelle image pour la remplacer. Les SVG sont conservés tels quels.')
                ->onlyOnForms(),
            Field::new('galleryUploads', 'Ajouter des photos de la boutique')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('multiple', true)
                ->setFormTypeOption('required', false)
                ->setFormTypeOption('constraints', [new All(constraints: [new File(
                    maxSize: '8M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/svg+xml'],
                    mimeTypesMessage: 'Veuillez sélectionner une image JPEG, PNG, WebP, AVIF ou SVG.',
                )])])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif,image/svg+xml'])
                ->setHelp('Sélectionnez plusieurs photos à ajouter à la galerie de cette boutique. Les photos existantes sont conservées.')
                ->onlyOnForms(),
            ChoiceField::new('horaireMode', 'Type d’horaire')->onlyOnForms()->setChoices([
                'Même horaire tous les jours' => 'fixe',
                'Horaire différent par jour' => 'jours',
            ])->setFormTypeOption('row_attr', ['data-controller' => 'schedule-form']),
            TextField::new('horaireFixe', 'Horaire fixe')->onlyOnForms()->setHelp('Exemple : 09:00 - 20:00'),
            TextField::new('horaireLundi', 'Lundi')->onlyOnForms(),
            TextField::new('horaireMardi', 'Mardi')->onlyOnForms(),
            TextField::new('horaireMercredi', 'Mercredi')->onlyOnForms(),
            TextField::new('horaireJeudi', 'Jeudi')->onlyOnForms(),
            TextField::new('horaireVendredi', 'Vendredi')->onlyOnForms(),
            TextField::new('horaireSamedi', 'Samedi')->onlyOnForms(),
            TextField::new('horaireDimanche', 'Dimanche')->onlyOnForms(),
        ]);
    }
}
