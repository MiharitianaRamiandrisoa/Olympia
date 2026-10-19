<?php

namespace App\Controller\Admin;

use App\Entity\Billet;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class BilletCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Billet::class;

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('reference', 'Reference')->hideOnForm(),
            AssociationField::new('reservation', 'Reservation')->hideOnForm(),
            ChoiceField::new('statut', 'Statut')->setChoices([
                'Valide' => Billet::STATUT_VALIDE,
                'Utilise' => Billet::STATUT_UTILISE,
                'Annule' => Billet::STATUT_ANNULE,
            ]),
            DateTimeField::new('dateEmission', 'Emis le')->hideOnForm(),
            DateTimeField::new('dateUtilisation', 'Utilise le')->hideOnForm(),
        ];
    }
}
