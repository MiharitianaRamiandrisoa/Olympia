<?php

namespace App\Command;

use App\Entity\Media;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed-demo-services', description: 'Ajoute des services de démonstration avec leurs images.')]
final class SeedDemoServicesCommand extends Command
{
    private const SERVICES = [
        [
            'nom' => 'Espace familles',
            'slug' => 'espace-familles',
            'description' => 'Un espace pensé pour les familles avec des équipements adaptés et un environnement confortable.',
            'image' => 'mascottes-famille.jpg',
            'horaires' => '10h00 - 21h00',
            'featured' => true,
        ],
        [
            'nom' => 'Animations enfants',
            'slug' => 'animations-enfants',
            'description' => 'Des activités créatives et des animations pour divertir les enfants pendant votre visite.',
            'image' => 'atelier-enfants.jpg',
            'horaires' => 'Selon le programme',
            'featured' => true,
        ],
        [
            'nom' => 'Parking gratuit',
            'slug' => 'parking-gratuit',
            'description' => 'Profitez de 2500 places de parking gratuites pour faciliter votre venue à Olympia.',
            'image' => 'hero-olympia.jpg',
            'horaires' => 'Ouvert aux horaires du centre',
            'featured' => true,
        ],
        [
            'nom' => 'Point information',
            'slug' => 'point-information',
            'description' => 'Notre équipe vous accompagne pour trouver une boutique, un restaurant ou une information pratique.',
            'image' => 'hero-boutiques.jpg',
            'telephone' => '+261 (0)34 234 56 67',
            'email' => 'contact@olympiacentre.com',
            'horaires' => '10h00 - 21h00',
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
        foreach (self::SERVICES as $data) {
            $service = $this->entityManager->getRepository(Service::class)->findOneBy(['slug' => $data['slug']]) ?? new Service();
            $service->setNom($data['nom'])
                ->setSlug($data['slug'])
                ->setDescription($data['description'])
                ->setTelephone($data['telephone'] ?? null)
                ->setEmail($data['email'] ?? null)
                ->setHoraires($data['horaires'])
                ->setEstActif(true)
                ->setEstMisEnAvant($data['featured']);

            $path = $root.'/public/images/demo/'.$data['image'];
            if (is_file($path)) {
                $media = $this->entityManager->getRepository(Media::class)->findOneBy(['nomFichier' => $data['image']]) ?? new Media();
                $media->setNomOriginal($data['image'])
                    ->setNomFichier($data['image'])
                    ->setChemin('images/demo/'.$data['image'])
                    ->setType('image')
                    ->setTypeMime('image/jpeg')
                    ->setTaille((string) filesize($path))
                    ->setTexteAlternatif($data['nom'])
                    ->setEstActif(true)
                    ->setDateCreation($media->getDateCreation() ?? new \DateTimeImmutable())
                    ->setDateModification(new \DateTimeImmutable());
                $this->entityManager->persist($media);
                $service->setPhotoMedia($media);
            }

            $this->entityManager->persist($service);
            $output->writeln(sprintf('<info>Service ajouté ou mis à jour : %s</info>', $data['nom']));
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
