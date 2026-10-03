<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Service\ImageOptimizer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\String\Slugger\SluggerInterface;

final class MediaCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Media::class;

    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly ImageOptimizer $imageOptimizer,
        SluggerInterface $slugger,
    )
    {
        parent::__construct($entityManager, $imageOptimizer, $slugger);
    }

    public function configureFields(string $pageName): iterable
    {
        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT], true)) {
            return [
                Field::new('sourceFile', 'Image à téléverser')
                    ->setFormType(FileType::class)
                    ->setFormTypeOption('mapped', true)
                    ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
                    ->setFormTypeOption('constraints', [new Image(maxSize: '8M', maxWidth: 8000, maxHeight: 8000)])
                    ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif']),
                TextareaField::new('texteAlternatif', 'Texte alternatif'),
                BooleanField::new('estActif', 'Image active'),
            ];
        }

        return parent::configureFields($pageName);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->prepareMedia($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->prepareMedia($entityInstance, false);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function prepareMedia(Media $media, bool $required = true): void
    {
        $file = $media->getSourceFile();
        if (!$file) {
            if ($required) {
                throw new \RuntimeException('Veuillez sélectionner une image.');
            }

            return;
        }

        $optimized = $this->imageOptimizer->convertToWebp($file);
        $media->setNomOriginal($file->getClientOriginalName())
            ->setNomFichier($optimized['filename'])
            ->setChemin($optimized['path'])
            ->setType('image')
            ->setTypeMime($optimized['mime'])
            ->setTaille((string) $optimized['size'])
            ->setDateCreation($media->getDateCreation() ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->setDateModification(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
    }
}
