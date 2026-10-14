<?php

namespace App\Command;

use App\Entity\Artiste;
use App\Entity\CategorieOeuvre;
use App\Entity\Media;
use App\Entity\Oeuvre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:galerie:seed-demo', description: 'Ajoute une collection de démonstration à la galerie')]
final class SeedGalerieDemoCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $artistes = [];
        foreach ([
            ['Miora R.', 'Peinture contemporaine'],
            ['Hery A.', 'Photographie'],
            ['Lalaina T.', 'Illustration'],
        ] as [$nom, $specialite]) {
            $artiste = $this->entityManager->getRepository(Artiste::class)->findOneBy(['nom' => $nom]) ?? (new Artiste())->setNom($nom);
            $artiste->setSpecialite($specialite)->setEstActif(true);
            $this->entityManager->persist($artiste);
            $artistes[$nom] = $artiste;
        }

        $categories = [];
        foreach (['Peinture', 'Photographie', 'Illustration'] as $nom) {
            $slug = strtolower(str_replace(' ', '-', $nom));
            $categorie = $this->entityManager->getRepository(CategorieOeuvre::class)->findOneBy(['slug' => $slug]) ?? (new CategorieOeuvre())->setNom($nom)->setSlug($slug);
            $categorie->setEstActif(true);
            $this->entityManager->persist($categorie);
            $categories[$nom] = $categorie;
        }

        $works = [
            ['Aube sur Tanjombato', 'Miora R.', 'Peinture', 'images/galerie/aube-sur-tanjombato.jpg', 'Huile sur toile', 1200, 900],
            ['Nuit des Baobabs', 'Hery A.', 'Photographie', 'images/galerie/nuit-des-baobabs.jpg', 'Tirage pigmentaire', 900, 1200],
            ["Vents d'Imerina", 'Lalaina T.', 'Illustration', 'images/galerie/vents-d-imerina.jpg', 'Encre et gouache', 1000, 1000],
            ['Rizières, matin', 'Miora R.', 'Peinture', 'images/galerie/rizieres-matin.jpg', 'Acrylique', 1400, 800],
            ['Colline verte', 'Hery A.', 'Photographie', 'images/galerie/colline-verte.jpg', 'Photographie', 800, 1200],
            ["Lune d'Antsirabe", 'Lalaina T.', 'Illustration', 'images/galerie/lune-d-antsirabe.jpg', 'Aquarelle', 1000, 700],
        ];

        foreach ($works as $index => [$titre, $artiste, $categorie, $url, $technique, $largeur, $hauteur]) {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $titre)), '-'));
            $oeuvre = $this->entityManager->getRepository(Oeuvre::class)->findOneBy(['slug' => $slug]) ?? (new Oeuvre())->setTitre($titre)->setSlug($slug);
            $oeuvre->setArtiste($artistes[$artiste])->setCategorie($categories[$categorie])->setTechnique($technique)->setAnnee(2026)->setOrdre($index)->setEstActive(true)->setEstMiseEnAvant($index < 3);
            $media = $oeuvre->getImageMedia() ?? (new Media());
            $media->setNomOriginal($titre.'.jpg')->setNomFichier(basename($url))->setChemin($url)->setType('image')->setTypeMime('image/jpeg')->setTaille('0')->setLargeur($largeur)->setHauteur($hauteur)->setTexteAlternatif($titre)->setEstActif(true)->setDateCreation(new \DateTimeImmutable())->setDateModification(new \DateTimeImmutable());
            $this->entityManager->persist($media);
            $oeuvre->setImageMedia($media);
            $this->entityManager->persist($oeuvre);
        }

        $this->entityManager->flush();
        $output->writeln('<info>La galerie de démonstration a été ajoutée.</info>');
        return Command::SUCCESS;
    }
}
