<?php
namespace App\Controller\Admin;
use App\Entity\Promotion;
use App\Entity\Media;
use App\Entity\Enseigne;
use App\Repository\BoutiqueRepository;
use App\Repository\RestaurantRepository;
use App\Service\ImageOptimizer;
use Doctrine\ORM\EntityManagerInterface;
use App\Filter\PromotionTypeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\String\Slugger\SluggerInterface;

final class PromotionCrudController extends AbstractEntityCrudController
{
    protected static string $entityClass = Promotion::class;

    public function __construct(
        EntityManagerInterface $entityManager,
        ImageOptimizer $imageOptimizer,
        private readonly BoutiqueRepository $boutiques,
        private readonly RestaurantRepository $restaurants,
        SluggerInterface $slugger,
    ) {
        parent::__construct($entityManager, $imageOptimizer, $slugger);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(PromotionTypeFilter::new());
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                ImageField::new('imageMedia', 'Image')
                    ->setBasePath('')
                    ->formatValue(static fn (?Media $media): ?string => $media?->getChemin()),
                TextField::new('titre', 'Titre'),
                TextareaField::new('description', 'Description'),
                TextareaField::new('conditions', 'Conditions'),
                DateTimeField::new('dateDebut', 'Date de début'),
                DateTimeField::new('dateFin', 'Date de fin'),
                BooleanField::new('estMisEnAvant', 'Mis en avant')->renderAsSwitch(),
            ];
        }

        $owners = [];
        foreach ($this->boutiques->findActive() as $boutique) {
            $enseigne = $boutique->getEnseigne();
            if ($enseigne) {
                $id = (string) $enseigne->getId();
                $owners[$id] = $enseigne;
            }
        }
        foreach ($this->restaurants->findActive() as $restaurant) {
            $enseigne = $restaurant->getEnseigne();
            if ($enseigne) {
                $id = (string) $enseigne->getId();
                $owners[$id] = $enseigne;
            }
        }

        $ownerField = Field::new('enseigne', 'Enseigne propriétaire')
            ->setFormType(EntityType::class)
            ->setFormTypeOption('class', Enseigne::class)
            ->setFormTypeOption('choices', array_values($owners))
            ->setFormTypeOption('choice_label', 'nom')
            ->setFormTypeOption('placeholder', 'Choisir l’enseigne propriétaire');

        return array_merge([
            $ownerField,
        ], iterator_to_array(parent::configureFields($pageName)), [
            Field::new('imageUpload', 'Image de la promotion')
                ->setFormType(FileType::class)
                ->setFormTypeOption('mapped', true)
                ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
                ->setFormTypeOption('constraints', [new Image(maxSize: '8M', maxWidth: 8000, maxHeight: 8000)])
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/avif'])
                ->setHelp('Laissez vide lors de la modification pour conserver l’image actuelle.')
                ->onlyOnForms(),
        ]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
    }
}
