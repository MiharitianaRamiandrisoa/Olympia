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
use Symfony\Component\Validator\Constraints\Image;

final class BoutiqueCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Boutique::class;

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
            Field::new('enseigneLogoUpload', 'Logo de l’enseigne')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', false)
                ->setFormTypeOption('constraints', [new Image(maxSize: '8M', maxWidth: 8000, maxHeight: 8000)])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif'])
                ->setHelp('Laissez vide pour conserver le logo actuel. Il sera converti automatiquement en WebP.')
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
                ->setFormTypeOption('constraints', [new Image(maxSize: '8M', maxWidth: 8000, maxHeight: 8000)])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif'])
                ->setHelp('Une photo déjà liée ? Laissez vide pour la conserver, ou choisissez une nouvelle photo pour la remplacer.')
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
