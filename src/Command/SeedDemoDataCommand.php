<?php

namespace App\Command;

use App\Entity\Boutique;
use App\Entity\CategorieBoutique;
use App\Entity\CategorieRestaurant;
use App\Entity\CategoriePromotion;
use App\Entity\CategorieEvenement;
use App\Entity\Enseigne;
use App\Entity\Promotion;
use App\Entity\Evenement;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:database:seed-demo', description: 'Ajoute les données de démonstration du frontend Olympia.')]
final class SeedDemoDataCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $boutiqueCategories = [];
        foreach (['Mode' => 'mode', 'Beauté & Santé' => 'beaute-sante', 'High-Tech' => 'high-tech', 'Alimentation' => 'alimentation'] as $name => $slug) {
            $category = $this->entityManager->getRepository(CategorieBoutique::class)->findOneBy(['slug' => $slug]) ?? new CategorieBoutique();
            $category->setNom($name)->setSlug($slug);
            $this->entityManager->persist($category);
            $boutiqueCategories[$slug] = $category;
        }

        $restaurantCategory = $this->entityManager->getRepository(CategorieRestaurant::class)->findOneBy(['slug' => 'cuisine-locale']) ?? new CategorieRestaurant();
        $restaurantCategory->setNom('Cuisine locale')->setSlug('cuisine-locale');
        $this->entityManager->persist($restaurantCategory);

        $shops = [
            ['ZARA', 'zara', 'mode'], ['H&M', 'hm', 'mode'], ['LC Waikiki', 'lc-waikiki', 'mode'],
            ['Orange', 'orange', 'high-tech'], ['Jumbo', 'jumbo', 'alimentation'],
        ];
        foreach ($shops as [$name, $slug, $categorySlug]) {
            $brand = $this->entityManager->getRepository(Enseigne::class)->findOneBy(['slug' => $slug]) ?? new Enseigne();
            $brand->setNom($name)->setSlug($slug)->setEstActif(true);
            $this->entityManager->persist($brand);
            if ($this->entityManager->getRepository(Boutique::class)->findOneBy(['enseigne' => $brand]) === null) {
                $boutique = (new Boutique())->setEnseigne($brand)->setCategorie($boutiqueCategories[$categorySlug])->setEstMisEnAvant(true);
                $this->entityManager->persist($boutique);
            }
        }

        $restaurants = [['Le Jardin', 'le-jardin'], ['Pizza & Co', 'pizza-co'], ['Sushi Time', 'sushi-time']];
        foreach ($restaurants as [$name, $slug]) {
            $brand = $this->entityManager->getRepository(Enseigne::class)->findOneBy(['slug' => $slug]) ?? new Enseigne();
            $brand->setNom($name)->setSlug($slug)->setEstActif(true);
            $this->entityManager->persist($brand);
            if ($this->entityManager->getRepository(Restaurant::class)->findOneBy(['enseigne' => $brand]) === null) {
                $restaurant = (new Restaurant())->setEnseigne($brand)->setCategorie($restaurantCategory)->setEstMisEnAvant(true);
                $this->entityManager->persist($restaurant);
            }
        }

        $promotionCategory = $this->entityManager->getRepository(CategoriePromotion::class)->findOneBy(['slug' => 'offres']) ?? new CategoriePromotion();
        $promotionCategory->setNom('Offres')->setSlug('offres');
        $this->entityManager->persist($promotionCategory);
        $promotionData = [
            ['Jusqu a -50% chez Zara', 'zara-50', 'zara'],
            ['Menu special', 'menu-special', 'le-jardin'],
            ['Offres exclusives', 'offres-exclusives', 'hm'],
        ];
        foreach ($promotionData as [$title, $slug, $brandSlug]) {
            if ($this->entityManager->getRepository(Promotion::class)->findOneBy(['slug' => $slug]) !== null) continue;
            $brand = $this->entityManager->getRepository(Enseigne::class)->findOneBy(['slug' => $brandSlug]);
            if ($brand === null) continue;
            $promotion = (new Promotion())->setEnseigne($brand)->setCategorie($promotionCategory)->setTitre($title)->setSlug($slug)
                ->setDescription('Profitez de cette offre proposee par Olympia.')->setDateDebut(new \DateTimeImmutable('-1 day'))
                ->setDateFin(new \DateTimeImmutable('+30 days'))->setEstMisEnAvant(true);
            $this->entityManager->persist($promotion);
        }

        $eventCategory = $this->entityManager->getRepository(CategorieEvenement::class)->findOneBy(['slug' => 'animations']) ?? new CategorieEvenement();
        $eventCategory->setNom('Animations')->setSlug('animations');
        $this->entityManager->persist($eventCategory);
        $eventData = [
            ['Concert live', 'concert-live', '+3 days'],
            ['Ateliers enfants', 'ateliers-enfants', '+7 days'],
            ['Animation a Olympia', 'animation-olympia', '+14 days'],
        ];
        foreach ($eventData as [$title, $slug, $start]) {
            if ($this->entityManager->getRepository(Evenement::class)->findOneBy(['slug' => $slug]) !== null) continue;
            $event = (new Evenement())->setCategorie($eventCategory)->setTitre($title)->setSlug($slug)
                ->setDescription('Retrouvez cette animation a Olympia.')->setDateDebut(new \DateTimeImmutable($start))
                ->setLieu('Place centrale')->setEstMisEnAvant(true);
            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();
        $output->writeln('<info>Données de démonstration ajoutées.</info>');
        return Command::SUCCESS;
    }
}
