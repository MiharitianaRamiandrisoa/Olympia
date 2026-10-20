<?php
namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use App\Service\ImageOptimizer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class UtilisateurCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Utilisateur::class;

    public function __construct(
        EntityManagerInterface $entityManager,
        ImageOptimizer $imageOptimizer,
        SluggerInterface $slugger,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct($entityManager, $imageOptimizer, $slugger);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('prenom', 'Prénom')->setRequired(false);
        yield TextField::new('nom', 'Nom');
        yield EmailField::new('email', 'Adresse e-mail');

        $passwordConstraints = [new Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.')];
        if ($pageName === Crud::PAGE_NEW) {
            array_unshift($passwordConstraints, new NotBlank(message: 'Le mot de passe est obligatoire.'));
        }

        yield TextField::new('motDePasse', 'Mot de passe')
            ->onlyOnForms()
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('always_empty', true)
            ->setFormTypeOption('attr', ['autocomplete' => $pageName === Crud::PAGE_NEW ? 'new-password' : 'new-password'])
            ->setFormTypeOption('constraints', $passwordConstraints)
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->setHelp($pageName === Crud::PAGE_EDIT ? 'Laissez vide pour conserver le mot de passe actuel.' : '8 caractères minimum.');

        yield ChoiceField::new('role', 'Rôle')->setChoices([
            'Utilisateur galerie' => 'ROLE_USER',
            'Contrôleur des billets' => 'ROLE_TICKET_CONTROL',
            'Administrateur' => 'ROLE_ADMIN',
        ]);
        yield BooleanField::new('estActif', 'Compte actif');
        yield DateTimeField::new('dateCreation', 'Créé le')->hideOnForm();
        yield DateTimeField::new('dateModification', 'Modifié le')->hideOnForm();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Utilisateur) {
            $this->hashPlainPassword($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Utilisateur) {
            $plainPassword = trim((string) $entityInstance->getMotDePasse());
            $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);

            if ($plainPassword === '') {
                $entityInstance->setMotDePasse((string) ($originalData['motDePasse'] ?? ''));
            } else {
                $this->hashPlainPassword($entityInstance);
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPlainPassword(Utilisateur $user): void
    {
        $password = (string) $user->getMotDePasse();
        if ($password === '' || password_get_info($password)['algo'] !== 0) {
            return;
        }

        $user->setMotDePasse($this->passwordHasher->hashPassword($user, $password));
    }
}
