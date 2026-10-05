<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use App\Entity\Boutique;
use App\Entity\AbonnementNewsletter;
use App\Entity\CategorieBoutique;
use App\Entity\CategorieEvenement;
use App\Entity\CategoriePromotion;
use App\Entity\CategorieRestaurant;
use App\Entity\Enseigne;
use App\Entity\Restaurant;
use App\Entity\Media;
use App\Entity\Promotion;
use App\Entity\Evenement;
use App\Entity\Service;
use App\Entity\InformationPratique;
use App\Entity\Actualite;
use App\Entity\MessageContact;
use App\Entity\Utilisateur;
use App\Service\ImageOptimizer;
use Symfony\Component\String\Slugger\SluggerInterface;

abstract class AbstractEntityCrudController extends AbstractCrudController
{
    protected static string $entityClass;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageOptimizer $imageOptimizer,
        private readonly SluggerInterface $slugger,
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return static::$entityClass;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('admin/form/photo_upload.html.twig')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter ' . $this->getPageEntityLabel())
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier ' . $this->getPageEntityLabel());
    }

    private function getPageEntityLabel(): string
    {
        return [
            Boutique::class => 'une boutique',
            Restaurant::class => 'un restaurant',
            Service::class => 'un service',
            Promotion::class => 'une promotion',
            Evenement::class => 'un evenement',
            InformationPratique::class => 'une information pratique',
            Actualite::class => 'une actualite',
            Media::class => 'un media',
            Utilisateur::class => 'un utilisateur',
            CategorieBoutique::class => 'une categorie de boutique',
            CategorieRestaurant::class => 'une categorie de restaurant',
            CategoriePromotion::class => 'une categorie de promotion',
            CategorieEvenement::class => 'une categorie d evenement',
            MessageContact::class => 'un message',
            AbonnementNewsletter::class => 'un abonne',
        ][static::$entityClass] ?? 'un element';
    }

    public function configureActions(Actions $actions): Actions
    {
        $labels = [
            Boutique::class => ['singular' => 'boutique', 'new' => 'Ajouter une boutique'],
            Restaurant::class => ['singular' => 'restaurant', 'new' => 'Ajouter un restaurant'],
            Service::class => ['singular' => 'service', 'new' => 'Ajouter un service'],
            Promotion::class => ['singular' => 'promotion', 'new' => 'Ajouter une promotion'],
            Evenement::class => ['singular' => 'événement', 'new' => 'Ajouter un événement'],
            InformationPratique::class => ['singular' => 'information pratique', 'new' => 'Ajouter une information'],
            Actualite::class => ['singular' => 'actualite', 'new' => 'Ajouter une actualite'],
            Media::class => ['singular' => 'média', 'new' => 'Ajouter un média'],
            Utilisateur::class => ['singular' => 'utilisateur', 'new' => 'Ajouter un utilisateur'],
            CategorieBoutique::class => ['singular' => 'catégorie de boutique', 'new' => 'Ajouter une catégorie de boutique'],
            CategorieRestaurant::class => ['singular' => 'catégorie de restaurant', 'new' => 'Ajouter une catégorie de restaurant'],
            CategoriePromotion::class => ['singular' => 'catégorie de promotion', 'new' => 'Ajouter une catégorie de promotion'],
            CategorieEvenement::class => ['singular' => 'catégorie d’événement', 'new' => 'Ajouter une catégorie d’événement'],
            MessageContact::class => ['singular' => 'message', 'new' => 'Ajouter un message'],
            AbonnementNewsletter::class => ['singular' => 'abonné', 'new' => 'Ajouter un abonné'],
        ][static::$entityClass] ?? ['singular' => 'élément', 'new' => 'Ajouter'];

        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action->setLabel($labels['new']))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action->setLabel('Modifier'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action->setLabel('Supprimer'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setLabel('Enregistrer'))
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, fn (Action $action) => $action->setLabel('Enregistrer et continuer'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn (Action $action) => $action->setLabel('Créer'))
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, fn (Action $action) => $action->setLabel('Créer et ajouter'));
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addAssetMapperEntry('app');
    }

    public function configureFields(string $pageName): iterable
    {
        $metadata = $this->entityManager->getClassMetadata(static::$entityClass);

        foreach ($metadata->getFieldNames() as $fieldName) {
            if ($fieldName === 'slug') {
                continue;
            }
            if ($fieldName === 'enseigne' && in_array(static::$entityClass, [Boutique::class, Restaurant::class, Promotion::class], true)) {
                continue;
            }
            if ($fieldName === 'horaires' && in_array(static::$entityClass, [Boutique::class, Restaurant::class], true)) {
                continue;
            }
            if ($fieldName === 'imageMedia' && in_array(static::$entityClass, [Promotion::class, Evenement::class, Actualite::class], true)) {
                continue;
            }
            if ($fieldName === 'photoMedia' && static::$entityClass === Service::class) {
                continue;
            }
            if ($fieldName === 'motDePasse') {
                continue;
            }
            if ($fieldName === 'id') {
                yield IdField::new($fieldName)->hideOnForm();
                continue;
            }
            if (in_array($fieldName, ['dateCreation', 'dateModification'], true)) {
                yield DateTimeField::new($fieldName)->hideOnForm();
                continue;
            }
            if ($metadata->hasAssociation($fieldName)) {
                yield AssociationField::new($fieldName);
                continue;
            }

            $type = $metadata->getTypeOfField($fieldName);
            if ($type === 'boolean') {
                yield BooleanField::new($fieldName);
            } elseif (in_array($type, ['datetime', 'datetime_immutable', 'datetimetz', 'datetimetz_immutable'], true)) {
                yield DateTimeField::new($fieldName);
            } elseif ($type === 'text') {
                yield TextareaField::new($fieldName);
            } else {
                yield TextField::new($fieldName);
            }
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->synchronizeEnseigne($entityManager, $entityInstance);
        $this->synchronizeSlug($entityInstance);
        $this->synchronizePhoto($entityManager, $entityInstance);
        $this->synchronizeImage($entityManager, $entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->synchronizeEnseigne($entityManager, $entityInstance);
        $this->synchronizeSlug($entityInstance);
        $this->synchronizePhoto($entityManager, $entityInstance);
        $this->synchronizeImage($entityManager, $entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function synchronizePhoto(EntityManagerInterface $entityManager, object $entity): void
    {
        if (!$entity instanceof Boutique && !$entity instanceof Restaurant) {
            return;
        }

        $photoUpload = $entity->getPhotoUpload();
        if (!$photoUpload) {
            return;
        }

        $optimized = $this->imageOptimizer->convertToWebp($photoUpload);
        $media = (new Media())
            ->setNomOriginal($photoUpload->getClientOriginalName())
            ->setNomFichier($optimized['filename'])
            ->setChemin($optimized['path'])
            ->setType('image')
            ->setTypeMime($optimized['mime'])
            ->setTaille((string) $optimized['size'])
            ->setTexteAlternatif((string) $entity)
            ->setEstActif(true)
            ->setDateCreation(new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->setDateModification(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $entityManager->persist($media);
        $entity->setPhotoMedia($media);
    }

    private function synchronizeImage(EntityManagerInterface $entityManager, object $entity): void
    {
        if (!$entity instanceof Promotion && !$entity instanceof Evenement && !$entity instanceof Service && !$entity instanceof Actualite) {
            return;
        }

        $imageUpload = $entity->getImageUpload();
        if (!$imageUpload) {
            return;
        }

        $optimized = $this->imageOptimizer->convertToWebp($imageUpload);
        $media = (new Media())
            ->setNomOriginal($imageUpload->getClientOriginalName())
            ->setNomFichier($optimized['filename'])
            ->setChemin($optimized['path'])
            ->setType('image')
            ->setTypeMime($optimized['mime'])
            ->setTaille((string) $optimized['size'])
            ->setTexteAlternatif((string) $entity)
            ->setEstActif(true)
            ->setDateCreation(new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->setDateModification(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $entityManager->persist($media);
        $entity->setImageMedia($media);
    }

    private function synchronizeEnseigne(EntityManagerInterface $entityManager, object $entity): void
    {
        if (!in_array($entity::class, [Boutique::class, Restaurant::class], true)) {
            return;
        }
        $enseigne = $entity->getEnseigne() ?? new Enseigne();
        $nom = trim((string) $entity->getEnseigneNom());
        $logo = $entity->getEnseigneLogoUpload();
        if ($logo) {
            $optimized = $this->imageOptimizer->convertToWebp($logo);
            $logoMedia = (new Media())
                ->setNomOriginal($logo->getClientOriginalName())
                ->setNomFichier($optimized['filename'])
                ->setChemin($optimized['path'])
                ->setType('image')
                ->setTypeMime($optimized['mime'])
                ->setTaille((string) $optimized['size'])
                ->setTexteAlternatif($nom)
                ->setEstActif(true)
                ->setDateCreation(new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
                ->setDateModification(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
            $entityManager->persist($logoMedia);
            $enseigne->setLogoMedia($logoMedia);
        }

        $enseigne->setNom($nom)
            ->setSlug($this->slugger->slug($nom)->lower()->toString())
            ->setDescription($entity->getEnseigneDescription())
            ->setTelephone($entity->getEnseigneTelephone())
            ->setEmail($entity->getEnseigneEmail())
            ->setLogoMedia($enseigne->getLogoMedia())
            ->setSiteWeb($entity->getEnseigneSiteWeb())
            ->setFacebook($entity->getEnseigneFacebook())
            ->setInstagram($entity->getEnseigneInstagram())
            ->setEstActif($entity->isEnseigneEstActif());
        $entity->setEnseigne($enseigne);
        $entity->setHoraires($entity->getHorairesFormData());
    }

    private function synchronizeSlug(object $entity): void
    {
        if (!method_exists($entity, 'setSlug')) {
            return;
        }

        $name = null;
        if (method_exists($entity, 'getTitre')) {
            $name = $entity->getTitre();
        } elseif (method_exists($entity, 'getNom')) {
            $name = $entity->getNom();
        }

        if (is_string($name) && trim($name) !== '') {
            $entity->setSlug($this->slugger->slug($name)->lower()->toString());
        }
    }
}
