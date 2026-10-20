<?php

namespace App\Controller\Admin;

use App\Entity\Evenement;
use App\Entity\Media;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;

final class EvenementCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Evenement::class;

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                ImageField::new('imageMedia', 'Image')
                    ->setBasePath('')
                    ->formatValue(static fn (?Media $media): ?string => $media?->getChemin()),
                TextField::new('titre', 'Titre'),
                DateTimeField::new('dateDebut', 'Debut'),
                IntegerField::new('capaciteMaximale', 'Capacite'),
                ChoiceField::new('typeBilletterie', 'Billetterie')->setChoices([
                    'Gratuit' => Evenement::TYPE_GRATUIT,
                    'Payant' => Evenement::TYPE_PAYANT,
                ]),
                BooleanField::new('estActif', 'Publie')->renderAsSwitch(),
                BooleanField::new('estMisEnAvant', 'Mis en avant')->renderAsSwitch(),
            ];
        }

        return [
            TextField::new('titre', 'Titre')->setRequired(true),
            AssociationField::new('categorie', 'Categorie'),
            TextareaField::new('description', 'Description')->setNumOfRows(6),
            TextareaField::new('informationsComplementaires', 'Informations complementaires')->setNumOfRows(4),
            DateTimeField::new('dateDebut', 'Date et heure de debut')->setRequired(true)->setFormTypeOption('with_seconds', false),
            DateTimeField::new('dateFin', 'Date et heure de fin')->setFormTypeOption('with_seconds', false),
            TextField::new('lieu', 'Lieu'),
            IntegerField::new('capaciteMaximale', 'Capacite maximale')->setRequired(true),
            DateTimeField::new('dateOuvertureReservations', 'Ouverture des reservations')->setRequired(true)->setFormTypeOption('with_seconds', false),
            DateTimeField::new('dateClotureReservations', 'Cloture des reservations')->setRequired(true)->setFormTypeOption('with_seconds', false),
            ChoiceField::new('typeBilletterie', 'Type de billetterie')->setChoices([
                'Gratuit' => Evenement::TYPE_GRATUIT,
                'Payant' => Evenement::TYPE_PAYANT,
            ]),
            TextField::new('tarif', 'Tarif (MGA)')->setHelp('Utilise uniquement pour preparer un evenement payant.'),
            BooleanField::new('estActif', 'Publie')->renderAsSwitch(),
            BooleanField::new('estMisEnAvant', 'Mis en avant')->renderAsSwitch(),
            Field::new('imageUpload', 'Image de evenement')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', false)
                ->setFormTypeOption('constraints', [new Image(maxSize: '8M', maxWidth: 8000, maxHeight: 8000)])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif'])
                ->setHelp('Facultatif. Une image par defaut sera utilisee si aucune image n est fournie.')
                ->onlyOnForms(),
        ];
    }
}
