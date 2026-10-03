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
use App\Entity\Boutique;
use App\Entity\Enseigne;
use App\Entity\Restaurant;
use App\Entity\Media;
use App\Entity\Promotion;
use App\Entity\Evenement;
use App\Entity\Service;
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
        return $crud->addFormTheme('admin/form/photo_upload.html.twig');
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
            if ($fieldName === 'imageMedia' && in_array(static::$entityClass, [Promotion::class, Evenement::class], true)) {
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
        if (!$entity instanceof Promotion && !$entity instanceof Evenement && !$entity instanceof Service) {
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
