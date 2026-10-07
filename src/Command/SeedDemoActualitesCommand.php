<?php

namespace App\Command;

use App\Entity\Actualite;
use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed-demo-actualites', description: 'Ajoute des actualités de démonstration avec leurs images.')]
final class SeedDemoActualitesCommand extends Command
{
    private const ACTUALITES = [
        [
            'titre' => 'Un week-end festif à Olympia',
            'slug' => 'week-end-festif-olympia',
            'categorie' => 'Vie du centre',
            'chapeau' => 'Retrouvez vos enseignes préférées, des animations et de belles surprises pour toute la famille.',
            'contenu' => 'Olympia vous accueille pour un week-end placé sous le signe du partage. Profitez des animations dans la galerie et découvrez les nouveautés de vos boutiques préférées.',
            'image' => 'hero-olympia.jpg',
            'chemin' => 'images/hero-olympia.jpg',
            'jours' => -1,
            'featured' => true,
        ],
        [
            'titre' => 'Les nouveautés mode de la saison',
            'slug' => 'nouveautes-mode-saison',
            'categorie' => 'Shopping',
            'chapeau' => 'Les collections de saison arrivent dans vos boutiques mode à Olympia.',
            'contenu' => 'Couleurs, matières et accessoires : faites le plein d’inspiration avec les nouvelles collections disponibles dans vos enseignes favorites.',
            'image' => 'zara.jpg',
            'jours' => -4,
            'featured' => false,
        ],
        [
            'titre' => 'Une pause gourmande pour toute la famille',
            'slug' => 'pause-gourmande-famille',
            'categorie' => 'Restauration',
            'chapeau' => 'Découvrez les adresses gourmandes d’Olympia pour déjeuner, goûter ou dîner ensemble.',
            'contenu' => 'Cuisine locale, recettes italiennes, spécialités asiatiques ou pause café : notre espace restauration vous accompagne à chaque moment de la journée.',
            'image' => 'restauration.jpg',
            'chemin' => 'images/restauration.jpg',
            'jours' => -7,
            'featured' => false,
        ],
        [
            'titre' => 'Atelier créatif pour les enfants',
            'slug' => 'atelier-creatif-enfants',
            'categorie' => 'Famille',
            'chapeau' => 'Les enfants sont invités à laisser parler leur imagination lors d’un atelier gratuit.',
            'contenu' => 'Rendez-vous dans l’espace animations pour un moment créatif encadré par notre équipe. Les places sont limitées et l’inscription se fait à l’accueil.',
            'image' => 'atelier-enfants.jpg',
            'jours' => -10,
            'featured' => false,
        ],
        [
            'titre' => 'Olympia accueille le marché des créateurs',
            'slug' => 'marche-createurs-olympia',
            'categorie' => 'Événement',
            'chapeau' => 'Venez rencontrer des créateurs malgaches et découvrir leurs savoir-faire.',
            'contenu' => 'Bijoux, décoration, mode et idées cadeaux : le marché des créateurs s’installe dans la galerie principale pour une journée de découvertes et de rencontres.',
            'image' => 'marche-createurs.jpg',
            'jours' => -14,
            'featured' => false,
        ],
        [
            'titre' => 'Une soirée musicale au cœur de la galerie',
            'slug' => 'soiree-musicale-galerie',
            'categorie' => 'Culture',
            'chapeau' => 'Profitez d’un concert live dans une ambiance conviviale après votre shopping.',
            'contenu' => 'Le centre Olympia vous propose une soirée musicale ouverte à tous. Installez-vous, profitez de la musique et partagez un moment chaleureux avec vos proches.',
            'image' => 'concert-live.jpg',
            'jours' => -18,
            'featured' => false,
        ],
    ];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $root = dirname(__DIR__, 2);
        $actualiteRepository = $this->entityManager->getRepository(Actualite::class);
        $mediaRepository = $this->entityManager->getRepository(Media::class);

        foreach (self::ACTUALITES as $data) {
            $relativeImagePath = $data['chemin'] ?? 'images/demo/'.$data['image'];
            $imagePath = $root.'/public/'.$relativeImagePath;
            if (!is_file($imagePath)) {
                $output->writeln(sprintf('<error>Image introuvable : %s</error>', $imagePath));
                return Command::FAILURE;
            }

            $media = $mediaRepository->findOneBy(['nomFichier' => $data['image']]) ?? new Media();
            $media->setNomOriginal($data['image'])
                ->setNomFichier($data['image'])
                ->setChemin($relativeImagePath)
                ->setType('image')
                ->setTypeMime('image/jpeg')
                ->setTaille((string) filesize($imagePath))
                ->setTexteAlternatif($data['titre'])
                ->setEstActif(true)
                ->setDateCreation($media->getDateCreation() ?? new \DateTimeImmutable())
                ->setDateModification(new \DateTimeImmutable());
            $this->entityManager->persist($media);

            $actualite = $actualiteRepository->findOneBy(['slug' => $data['slug']]) ?? new Actualite();
            $actualite->setTitre($data['titre'])
                ->setSlug($data['slug'])
                ->setCategorie($data['categorie'])
                ->setChapeau($data['chapeau'])
                ->setContenu($data['contenu'])
                ->setImageMedia($media)
                ->setDatePublication(new \DateTimeImmutable($data['jours'].' days'))
                ->setEstActif(true)
                ->setEstMisEnAvant($data['featured'])
                ->setDateModification(new \DateTimeImmutable());
            $this->entityManager->persist($actualite);

            $output->writeln(sprintf('<info>Actualité ajoutée ou mise à jour : %s</info>', $data['titre']));
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
