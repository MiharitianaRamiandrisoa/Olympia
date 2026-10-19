<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class ReservationCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Reservation::class;

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('reference', 'Reference')->hideOnForm(),
            AssociationField::new('evenement', 'Evenement'),
            TextField::new('nom', 'Nom'),
            TextField::new('email', 'Email'),
            TextField::new('telephone', 'Telephone'),
            IntegerField::new('nombrePlaces', 'Places'),
            ChoiceField::new('statut', 'Statut')->setChoices([
                'Confirmee' => Reservation::STATUT_CONFIRMEE,
                'En attente de paiement' => Reservation::STATUT_EN_ATTENTE_PAIEMENT,
                'Annulee' => Reservation::STATUT_ANNULEE,
            ]),
            ChoiceField::new('statutPaiement', 'Paiement')->setChoices([
                'Non applicable' => Reservation::PAIEMENT_NON_APPLICABLE,
                'En attente' => Reservation::PAIEMENT_EN_ATTENTE,
                'Paye' => Reservation::PAIEMENT_PAYE,
            ]),
            DateTimeField::new('dateCreation', 'Creee le')->hideOnForm(),
            DateTimeField::new('dateConfirmation', 'Confirmee le')->hideOnForm(),
            AssociationField::new('billets', 'Billets')->onlyOnDetail(),
        ];
    }
}
