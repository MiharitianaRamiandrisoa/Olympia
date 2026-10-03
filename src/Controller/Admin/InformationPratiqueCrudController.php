<?php

namespace App\Controller\Admin;

use App\Entity\InformationPratique;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

final class InformationPratiqueCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = InformationPratique::class;

    public function configureFields(string $pageName): iterable
    {
        return [
            ChoiceField::new('type', 'Type')->setChoices([
                'Horaires' => 'horaires',
                'Adresse' => 'adresse',
                'Parking gratuit' => 'parking',
                'Contact' => 'contact',
            ]),
            TextField::new('titre', 'Titre'),
            TextareaField::new('contenu', 'Contenu'),
            IntegerField::new('ordreAffichage', 'Ordre'),
            BooleanField::new('estActif', 'Actif')->renderAsSwitch(),
        ];
    }
}
