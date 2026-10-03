<?php

namespace App\Command;

use App\Entity\AbonnementNewsletter;
use App\Entity\Boutique;
use App\Entity\CategorieBoutique;
use App\Entity\CategorieEvenement;
use App\Entity\CategoriePromotion;
use App\Entity\CategorieRestaurant;
use App\Entity\Enseigne;
use App\Entity\Evenement;
use App\Entity\InformationPratique;
use App\Entity\MessageContact;
use App\Entity\Promotion;
use App\Entity\Restaurant;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:database:reset-demo', description: 'Réinitialise les données fonctionnelles et charge un jeu réaliste Olympia.')]
final class ResetAndSeedDemoDataCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Confirme la suppression des données fonctionnelles.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('force')) {
            $output->writeln('<comment>Cette commande supprime les données fonctionnelles. Relance avec --force.</comment>');
            return Command::INVALID;
        }

        $this->clearFunctionalData();
        $this->seed();
        $output->writeln('<info>Données Olympia réinitialisées avec succès.</info>');
        $output->writeln('<comment>Le compte administrateur existant a été conservé.</comment>');
        return Command::SUCCESS;
    }

    private function clearFunctionalData(): void
    {
        $tables = [
            'abonnement_newsletter', 'message_contact', 'evenement', 'promotion', 'restaurant', 'boutique',
            'service', 'information_pratique', 'media', 'categorie_evenement',
            'categorie_promotion', 'categorie_restaurant', 'categorie_boutique', 'enseigne',
        ];
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) $this->connection->executeStatement('DELETE FROM '.$table);
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        $this->entityManager->clear();
    }

    private function seed(): void
    {
        $boutiqueCategories = $this->categories(CategorieBoutique::class, [
            ['Mode', 'mode'], ['Beauté & santé', 'beaute-sante'], ['Maison', 'maison'], ['High-tech', 'high-tech'], ['Alimentation', 'alimentation'],
        ]);
        $restaurantCategories = $this->categories(CategorieRestaurant::class, [
            ['Cuisine locale', 'cuisine-locale'], ['Italienne', 'italienne'], ['Asiatique', 'asiatique'], ['Fast-food', 'fast-food'], ['Café & pâtisserie', 'cafe-patisserie'],
        ]);
        $promotionCategories = $this->categories(CategoriePromotion::class, [
            ['Mode', 'mode'], ['Restauration', 'restauration'], ['Maison', 'maison'], ['Alimentation', 'alimentation'], ['Services', 'services'],
        ]);
        $eventCategories = $this->categories(CategorieEvenement::class, [
            ['Animations', 'animations'], ['Culture', 'culture'], ['Famille', 'famille'],
        ]);

        $shopData = [
            ['Zara', 'zara', 'mode', 'Mode femme et homme'], ['H&M', 'hm', 'mode', 'Mode accessible pour toute la famille'],
            ['LC Waikiki', 'lc-waikiki', 'mode', 'Mode familiale et accessoires'], ['Maison du Monde', 'maison-du-monde', 'maison', 'Décoration et art de vivre'],
            ['Techno Store', 'techno-store', 'high-tech', 'Téléphonie et accessoires connectés'], ['Jumbo', 'jumbo', 'alimentation', 'Supermarché et produits du quotidien'],
            ['Beauty Corner', 'beauty-corner', 'beaute-sante', 'Cosmétiques et soins'], ['Urban Shoes', 'urban-shoes', 'mode', 'Chaussures et maroquinerie'],
        ];
        $brands = [];
        foreach ($shopData as [$name, $slug, $category, $description]) {
            $brand = (new Enseigne())->setNom($name)->setSlug($slug)->setDescription($description)->setEstActif(true);
            $this->entityManager->persist($brand);
            $brands[$slug] = $brand;
            $this->entityManager->persist((new Boutique())->setEnseigne($brand)->setCategorie($boutiqueCategories[$category])->setDescription($description.' — Olympia.')->setHoraires(['mode' => 'fixe', 'fixe' => '09:00 - 20:00'])->setEstMisEnAvant(in_array($slug, ['zara', 'jumbo', 'techno-store'], true)));
        }

        $restaurantData = [
            ['Le Jardin', 'le-jardin', 'cuisine-locale', 'Cuisine malgache contemporaine'], ['Pizza & Co', 'pizza-co', 'italienne', 'Pizzas et recettes italiennes'],
            ['Sushi Time', 'sushi-time', 'asiatique', 'Sushis et cuisine japonaise'], ['Burger House', 'burger-house', 'fast-food', 'Burgers gourmets et menus familiaux'],
            ['Café des Amis', 'cafe-des-amis', 'cafe-patisserie', 'Cafés, jus frais et pâtisseries maison'], ['Saveurs d’Asie', 'saveurs-asie', 'asiatique', 'Cuisine asiatique à partager'],
        ];
        foreach ($restaurantData as [$name, $slug, $category, $description]) {
            $brand = (new Enseigne())->setNom($name)->setSlug($slug)->setDescription($description)->setEstActif(true);
            $this->entityManager->persist($brand);
            $brands[$slug] = $brand;
            $this->entityManager->persist((new Restaurant())->setEnseigne($brand)->setCategorie($restaurantCategories[$category])->setDescription($description.' — Olympia.')->setHoraires(['mode' => 'jours', 'jours' => ['lundi' => '11:00 - 21:00', 'mardi' => '11:00 - 21:00', 'mercredi' => '11:00 - 21:00', 'jeudi' => '11:00 - 22:00', 'vendredi' => '11:00 - 22:00', 'samedi' => '10:00 - 22:00', 'dimanche' => '10:00 - 20:00']])->setEstMisEnAvant(in_array($slug, ['le-jardin', 'cafe-des-amis'], true)));
        }

        $promotions = [
            ['-30% sur la collection automne', 'zara-automne', 'zara', 'mode', -2, 28], ['Deux pizzas achetées, la troisième offerte', 'pizza-trois-pour-deux', 'pizza-co', 'restauration', -1, 18],
            ['Semaine maison : jusqu’à -25%', 'maison-semaine', 'maison-du-monde', 'maison', 0, 12], ['Menu famille à prix doux', 'jumbo-menu-famille', 'jumbo', 'alimentation', 2, 22],
            ['Café gourmand offert dès 30 000 Ar', 'cafe-gourmand', 'cafe-des-amis', 'restauration', 3, 35],
        ];
        foreach ($promotions as [$title, $slug, $brand, $category, $start, $days]) {
            $this->entityManager->persist((new Promotion())->setEnseigne($brands[$brand])->setCategorie($promotionCategories[$category])->setTitre($title)->setSlug($slug)->setDescription('Profitez de cette offre exclusive dans votre centre Olympia.')->setConditions('Offre valable dans la limite des stocks disponibles.')->setDateDebut(new \DateTimeImmutable($start.' days'))->setDateFin(new \DateTimeImmutable('+'.$days.' days'))->setEstMisEnAvant($start <= 0));
        }

        $events = [
            ['Concert live au coucher du soleil', 'concert-live-coucher', 'culture', '+5 days', 'Place centrale'], ['Atelier créatif pour enfants', 'atelier-creatif-enfants', 'famille', '+9 days', 'Espace animations'],
            ['Marché des créateurs malgaches', 'marche-createurs', 'culture', '+16 days', 'Galerie principale'], ['Mascottes et photos en famille', 'mascottes-famille', 'famille', '+23 days', 'Atrium Olympia'],
        ];
        foreach ($events as [$title, $slug, $category, $date, $place]) {
            $this->entityManager->persist((new Evenement())->setCategorie($eventCategories[$category])->setTitre($title)->setSlug($slug)->setDescription('Un rendez-vous convivial pour toute la famille à Olympia.')->setInformationsComplementaires('Entrée libre dans la limite des places disponibles.')->setDateDebut(new \DateTimeImmutable($date.' 18:00'))->setLieu($place)->setEstMisEnAvant(true));
        }

        $this->entityManager->persist((new MessageContact())->setNom('Mamy Rakoto')->setEmail('mamy.rakoto@example.com')->setSujet('Horaires du centre')->setMessage('Bonjour, pouvez-vous me confirmer les horaires du dimanche ?'));
        $this->entityManager->persist((new MessageContact())->setNom('Jean Andria')->setEmail('jean.andria@example.com')->setSujet('Location d’espace')->setMessage('Je souhaite obtenir des informations pour un événement professionnel.'));
        $this->entityManager->persist((new AbonnementNewsletter())->setEmail('client.fidele@example.com'));
        foreach ([
            ['horaires', 'Horaires', "Lundi - Samedi : 10h00 - 21h00\nDimanche : 10h00 - 15h00"],
            ['parking', 'Parking gratuit', '2500 places'],
            ['adresse', 'Localisation', "Centre commercial Olympia\nTanjombato, Madagascar"],
        ] as $order => [$type, $title, $content]) {
            $this->entityManager->persist((new InformationPratique())
                ->setType($type)->setTitre($title)->setContenu($content)->setOrdreAffichage($order));
        }
        $this->entityManager->flush();
    }

    private function categories(string $class, array $data): array
    {
        $result = [];
        foreach ($data as [$name, $slug]) {
            $category = new $class();
            $category->setNom($name)->setSlug($slug);
            $this->entityManager->persist($category);
            $result[$slug] = $category;
        }
        return $result;
    }
}
