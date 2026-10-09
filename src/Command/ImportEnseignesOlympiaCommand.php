<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Boutique;
use App\Entity\CategorieBoutique;
use App\Entity\CategorieRestaurant;
use App\Entity\Enseigne;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(
    name: 'app:database:import-enseignes-olympia',
    description: 'Importe la liste des enseignes Olympia fournie dans le document source.',
)]
final class ImportEnseignesOlympiaCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $slugger = new AsciiSlugger();
        $boutiqueCategories = $this->getBoutiqueCategories();
        $restaurantCategory = $this->getRestaurantCategory();
        $created = 0;
        $updated = 0;

        foreach ($this->enseigneData() as $data) {
            $slug = strtolower((string) $slugger->slug($data['nom']));
            $enseigne = $this->entityManager->getRepository(Enseigne::class)->findOneBy(['slug' => $slug]);
            $isNew = $enseigne === null;
            $enseigne ??= new Enseigne();

            $enseigne
                ->setNom($data['nom'])
                ->setSlug($slug)
                ->setDescription(sprintf('Activité : %s\nEmplacement : %s', $data['activite'], $data['emplacement']))
                ->setEstActif(true);

            $this->entityManager->persist($enseigne);
            $isNew ? ++$created : ++$updated;

            if ($data['type'] === 'restaurant') {
                $restaurant = $this->entityManager->getRepository(Restaurant::class)->findOneBy(['enseigne' => $enseigne]);
                $restaurant ??= (new Restaurant())->setEnseigne($enseigne);
                $restaurant
                    ->setCategorie($restaurantCategory)
                    ->setDescription($data['activite'])
                    ->setEstActif(true);
                $this->entityManager->persist($restaurant);
                continue;
            }

            $boutique = $this->entityManager->getRepository(Boutique::class)->findOneBy(['enseigne' => $enseigne]);
            $boutique ??= (new Boutique())->setEnseigne($enseigne);
            $boutique
                ->setCategorie($boutiqueCategories[$data['categorie']])
                ->setDescription($data['activite'])
                ->setEstActif(true);
            $this->entityManager->persist($boutique);
        }

        $this->entityManager->flush();
        $output->writeln(sprintf('<info>Import terminé : %d créées, %d mises à jour.</info>', $created, $updated));

        return Command::SUCCESS;
    }

    /** @return array<string, CategorieBoutique> */
    private function getBoutiqueCategories(): array
    {
        $categories = [
            'services' => 'Services',
            'mode' => 'Mode',
            'beaute-sante' => 'Beauté et santé',
            'high-tech' => 'High tech',
            'maison' => 'Maison et décoration',
            'enfant' => 'Enfant',
            'culture' => 'Culture et loisirs',
            'alimentation' => 'Alimentation',
        ];
        $result = [];

        foreach ($categories as $slug => $name) {
            $category = $this->entityManager->getRepository(CategorieBoutique::class)->findOneBy(['slug' => $slug]);
            $category ??= new CategorieBoutique();
            $category->setNom($name)->setSlug($slug)->setEstActif(true);
            $this->entityManager->persist($category);
            $result[$slug] = $category;
        }

        return $result;
    }

    private function getRestaurantCategory(): CategorieRestaurant
    {
        $category = $this->entityManager->getRepository(CategorieRestaurant::class)->findOneBy(['slug' => 'restauration']);
        $category ??= new CategorieRestaurant();
        $category->setNom('Restauration')->setSlug('restauration')->setEstActif(true);
        $this->entityManager->persist($category);

        return $category;
    }

    /** @return list<array{emplacement: string, nom: string, activite: string, type: string, categorie: string}> */
    private function enseigneData(): array
    {
        return [
            ['emplacement' => 'RDC Box 1-2', 'nom' => 'AFG BANK', 'activite' => 'Agence bancaire et GAB.', 'type' => 'boutique', 'categorie' => 'services'],
            ['emplacement' => 'RDC Box 3', 'nom' => 'BLUE CAFE', 'activite' => 'Pâtisserie et viennoiserie.', 'type' => 'restaurant', 'categorie' => 'alimentation'],
            ['emplacement' => 'RDC Kiosque', 'nom' => 'CANAL+', 'activite' => 'Commercialisation d’équipements et de biens concourant à la réception des programmes.', 'type' => 'boutique', 'categorie' => 'services'],
            ['emplacement' => 'R+1 Box 7', 'nom' => 'SKINSHARE', 'activite' => 'Vente de produits cosmétiques de marques coréennes.', 'type' => 'boutique', 'categorie' => 'beaute-sante'],
            ['emplacement' => 'R+1 Box 9', 'nom' => 'LES CITADINES', 'activite' => 'Prêt-à-porter, accessoires et robes de mariée.', 'type' => 'boutique', 'categorie' => 'mode'],
            ['emplacement' => 'R+1 Box 10', 'nom' => 'JAINTILAL K', 'activite' => 'Bijouterie.', 'type' => 'boutique', 'categorie' => 'mode'],
            ['emplacement' => 'R+1 Box 11', 'nom' => "L'INSTANT MODE PRIME", 'activite' => 'Women concept store.', 'type' => 'boutique', 'categorie' => 'mode'],
            ['emplacement' => 'R+1 Box 12', 'nom' => 'WOMEN SECRET', 'activite' => 'Lingerie.', 'type' => 'boutique', 'categorie' => 'mode'],
            ['emplacement' => 'R+1 Box 15', 'nom' => 'HAPPY SPORT', 'activite' => 'Prêt-à-porter homme et femme, vêtements et accessoires de sport.', 'type' => 'boutique', 'categorie' => 'mode'],
            ['emplacement' => 'R+1 Box 16', 'nom' => 'HAPPY SHOP', 'activite' => 'Commerce de détail.', 'type' => 'boutique', 'categorie' => 'mode'],
            ['emplacement' => 'R+1 Box 17', 'nom' => 'HAPPY LADIES', 'activite' => 'Prêt-à-porter féminin.', 'type' => 'boutique', 'categorie' => 'mode'],
            ['emplacement' => 'R+1 Box 18', 'nom' => 'IENDRIKO', 'activite' => 'Produits et accessoires Vita Malagasy, épicerie fine, produits fermiers et boissons locales.', 'type' => 'boutique', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+1 Box 19', 'nom' => 'AXIUS', 'activite' => 'Produits high-tech : smartphones, informatique et accessoires.', 'type' => 'boutique', 'categorie' => 'high-tech'],
            ['emplacement' => 'R+1 Box 20', 'nom' => 'HOMEOPHARMA', 'activite' => 'Produits naturels : homéopathie, phytothérapie, aromathérapie et cosmétiques naturels.', 'type' => 'boutique', 'categorie' => 'beaute-sante'],
            ['emplacement' => 'Kiosque R+1', 'nom' => "SENS'OR", 'activite' => 'Produits olfactifs, hors produits dérivés des huiles essentielles et gammes cosmétiques.', 'type' => 'boutique', 'categorie' => 'beaute-sante'],
            ['emplacement' => 'Kiosque R+1', 'nom' => 'MISK & FLEUR FANTAISIE DE DUBAI', 'activite' => 'Articles de fantaisie et parfumerie.', 'type' => 'boutique', 'categorie' => 'beaute-sante'],
            ['emplacement' => 'R+2 Box 21', 'nom' => 'ZAZAH COLLECTION', 'activite' => 'Articles de bébé, puériculture et mode enfantine.', 'type' => 'boutique', 'categorie' => 'enfant'],
            ['emplacement' => 'R+2 Box 22', 'nom' => 'MSL Mercerie', 'activite' => 'Mercerie.', 'type' => 'boutique', 'categorie' => 'culture'],
            ['emplacement' => 'R+2 Box 23', 'nom' => 'APRYIL BOUTIK', 'activite' => 'Articles de maison et home supply.', 'type' => 'boutique', 'categorie' => 'maison'],
            ['emplacement' => 'R+2 Box 24', 'nom' => 'WALLS', 'activite' => 'Produits locaux et artisanaux.', 'type' => 'boutique', 'categorie' => 'maison'],
            ['emplacement' => 'R+2 Box 25', 'nom' => 'NET A SEC', 'activite' => 'Dépôt et livraison de linge.', 'type' => 'boutique', 'categorie' => 'services'],
            ['emplacement' => 'R+2 Box 26', 'nom' => 'PAPER STORE', 'activite' => 'Fournitures bureautiques et scolaires, mobilier de bureau, outils éducatifs et matériel d’art et de loisirs créatifs.', 'type' => 'boutique', 'categorie' => 'culture'],
            ['emplacement' => 'R+2 Box 30', 'nom' => 'PASSERELLE Librairie solidaire', 'activite' => 'Librairie, magazines, revues, journaux, papeterie créative, carterie et animations culturelles.', 'type' => 'boutique', 'categorie' => 'culture'],
            ['emplacement' => 'R+2 Box 31', 'nom' => 'WATSON', 'activite' => 'Matériel de sécurité et informatique.', 'type' => 'boutique', 'categorie' => 'high-tech'],
            ['emplacement' => 'R+2 Box 33', 'nom' => 'NATIONAL CENTER', 'activite' => 'Matériel de sonorisation et instruments de musique.', 'type' => 'boutique', 'categorie' => 'culture'],
            ['emplacement' => 'R+2 Box 35', 'nom' => 'RHUM 303', 'activite' => 'Distribution de rhum.', 'type' => 'boutique', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+2 Box 36', 'nom' => 'LAITERIE MAMINAIANA', 'activite' => 'Laiterie et crèmerie.', 'type' => 'boutique', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+2 HALL', 'nom' => 'ANDRY GARDEN', 'activite' => 'Pépiniériste et paysagiste.', 'type' => 'boutique', 'categorie' => 'maison'],
            ['emplacement' => 'Coin sortie terrasse', 'nom' => 'BERNADETTE DE LAVERNETTE', 'activite' => 'Glacier, crêperie, restauration légère, snacking sucré-salé et boissons chaudes et froides.', 'type' => 'restaurant', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+2 Box 38', 'nom' => 'HIRONDELLE', 'activite' => 'Restauration asiatique, boissons, snacking et desserts.', 'type' => 'restaurant', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+2 Box 39', 'nom' => 'PAPA OURS CAFE', 'activite' => 'Restauration rapide spécialisée dans les desserts et crèmes glacées.', 'type' => 'restaurant', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+2 Box 42', 'nom' => 'SAFRAN', 'activite' => "Cuisine de l’Orient.", 'type' => 'restaurant', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+2 Box 43', 'nom' => 'MAISON CREPE by TASTY WAY', 'activite' => 'Crêperie, gaufrierie et snacking.', 'type' => 'restaurant', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+2 Box 44', 'nom' => 'RED BOWL', 'activite' => 'Spécialités asiatiques.', 'type' => 'restaurant', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+2 Box 45', 'nom' => 'PASTA ET GELATO', 'activite' => 'Pâtes fraîches artisanales express et glace artisanale.', 'type' => 'restaurant', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+2 Box 46', 'nom' => 'SWAAD', 'activite' => 'Cuisine mixte orientale et chinoise.', 'type' => 'restaurant', 'categorie' => 'alimentation'],
            ['emplacement' => 'R+2 Box 47', 'nom' => 'TERRE MER GOURMET', 'activite' => 'Plats de fruits de mer, plats espagnols et malgaches.', 'type' => 'restaurant', 'categorie' => 'alimentation'],
        ];
    }
}
