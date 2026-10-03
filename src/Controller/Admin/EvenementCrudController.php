<?php
namespace App\Controller\Admin;
use App\Entity\Evenement;
use App\Entity\Media;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
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
                TextareaField::new('description', 'Description'),
                DateTimeField::new('dateDebut', 'Date de début'),
                BooleanField::new('estMisEnAvant', 'Mis en avant')->renderAsSwitch(),
            ];
        }

        return array_merge(iterator_to_array(parent::configureFields($pageName)), [
            Field::new('imageUpload', 'Image de l’événement')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', false)
                ->setFormTypeOption('constraints', [new Image(maxSize: '8M', maxWidth: 8000, maxHeight: 8000)])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif'])
                ->setHelp('Facultatif. Une image par défaut sera utilisée si aucune image n’est fournie.')
                ->onlyOnForms(),
        ]);
    }
}
