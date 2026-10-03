<?php

namespace App\Command;

use App\Entity\Boutique;
use App\Entity\Evenement;
use App\Entity\Media;
use App\Entity\Promotion;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:seed-demo-images', description: 'Télécharge et associe des images réalistes aux données de démonstration.')]
final class SeedDemoImagesCommand extends Command
{
    private const STATIC_IMAGES = [
        'hero-olympia.jpg' => 'photo-1780510995351-494218aa155c',
        'hero-boutiques.jpg' => 'photo-1441986300917-64674bd600d8',
        'hero-restaurants.jpg' => 'photo-1517248135467-4c7edcad34c4',
        'hero-promotions.jpg' => 'photo-1525507119028-ed4c629a60a3',
        'restauration.jpg' => 'photo-1504674900247-0877df9cc836',
    ];

    private const IMAGES = [
        Boutique::class => [
            'zara' => ['zara.jpg', 'photo-1445205170230-053b83016050'],
            'hm' => ['hm.jpg', 'photo-1551488831-00ddcb6c6bd3'],
            'lc-waikiki' => ['lc-waikiki.jpg', 'photo-1490481651871-ab68de25d43d'],
            'maison-du-monde' => ['maison-du-monde.jpg', 'photo-1618221195710-dd6b41faaea6'],
            'techno-store' => ['techno-store.jpg', 'photo-1511707171634-5f897ff02aa9'],
            'jumbo' => ['jumbo.jpg', 'photo-1542838132-92c53300491e'],
            'beauty-corner' => ['beauty-corner.jpg', 'photo-1596462502278-27bfdc403348'],
            'urban-shoes' => ['urban-shoes.jpg', 'photo-1542291026-7eec264c27ff'],
        ],
        Restaurant::class => [
            'le-jardin' => ['le-jardin.jpg', 'photo-1517248135467-4c7edcad34c4'],
            'pizza-co' => ['pizza-co.jpg', 'photo-1574071318508-1cdbab80d002'],
            'sushi-time' => ['sushi-time.jpg', 'photo-1579871494447-9811cf80d66c'],
            'burger-house' => ['burger-house.jpg', 'photo-1568901346375-23c9450c58cd'],
            'cafe-des-amis' => ['cafe-des-amis.jpg', 'photo-1509042239860-f550ce710b93'],
            'saveurs-asie' => ['saveurs-asie.jpg', 'photo-1515003197210-e0cd71810b5f'],
        ],
        Promotion::class => [
            'zara-automne' => ['promotion-zara.jpg', 'photo-1441986300917-64674bd600d8'],
            'pizza-trois-pour-deux' => ['promotion-pizza.jpg', 'photo-1579751626657-72bc17010498'],
            'maison-semaine' => ['promotion-maison.jpg', 'photo-1555041469-a586c61ea9bc'],
            'jumbo-menu-famille' => ['promotion-jumbo.jpg', 'photo-1606787366850-de6330128bfc'],
            'cafe-gourmand' => ['promotion-cafe.jpg', 'photo-1495474472287-4d71bcdd2085'],
        ],
        Evenement::class => [
            'concert-live-coucher' => ['concert-live.jpg', 'photo-1501386761578-eac5c94b800a'],
            'atelier-creatif-enfants' => ['atelier-enfants.jpg', 'photo-1452421822248-d4c2b47f0c81'],
            'marche-createurs' => ['marche-createurs.jpg', 'photo-1488459716781-31db52582fe9'],
            'mascottes-famille' => ['mascottes-famille.jpg', 'photo-1472162072942-cd5147eb3902'],
        ],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectRoot = dirname(__DIR__, 2);
        $directory = $projectRoot.'/public/images/demo';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        foreach (self::STATIC_IMAGES as $filename => $photoId) {
            try {
                $response = $this->httpClient->request('GET', 'https://images.unsplash.com/'.$photoId.'?auto=format&fit=crop&w=1800&q=84');
                file_put_contents($projectRoot.'/public/images/'.$filename, $response->getContent());
                $output->writeln(sprintf('<info>Image de présentation ajoutée : %s</info>', $filename));
            } catch (\Throwable $exception) {
                $output->writeln(sprintf('<error>Échec pour %s : %s</error>', $filename, $exception->getMessage()));
            }
        }

        $associated = 0;
        foreach (self::IMAGES as $class => $items) {
            foreach ($items as $slug => [$filename, $photoId]) {
                $entity = $this->findEntity($class, $slug);
                if (!$entity) {
                    $output->writeln(sprintf('<comment>Introuvable : %s (%s)</comment>', $slug, $class));
                    continue;
                }

                $path = $directory.'/'.$filename;
                if (!is_file($path)) {
                    try {
                        $response = $this->httpClient->request('GET', 'https://images.unsplash.com/'.$photoId.'?auto=format&fit=crop&w=1400&q=82');
                        $content = $response->getContent();
                        file_put_contents($path, $content);
                    } catch (\Throwable $exception) {
                        $output->writeln(sprintf('<error>Échec pour %s : %s</error>', $slug, $exception->getMessage()));
                        continue;
                    }
                } else {
                    $output->writeln(sprintf('<comment>Image locale réutilisée : %s</comment>', $filename));
                }

                $media = $this->entityManager->getRepository(Media::class)->findOneBy(['nomFichier' => $filename]) ?? new Media();
                $media->setNomOriginal($filename)
                    ->setNomFichier($filename)
                    ->setChemin('images/demo/'.$filename)
                    ->setType('image')
                    ->setTypeMime('image/jpeg')
                    ->setTaille((string) filesize($path))
                    ->setTexteAlternatif((string) $entity)
                    ->setEstActif(true)
                    ->setDateCreation($media->getDateCreation() ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
                    ->setDateModification(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
                $this->entityManager->persist($media);

                if ($entity instanceof Boutique) {
                    $entity->setPhotoMedia($media);
                } elseif ($entity instanceof Restaurant) {
                    $entity->setPhotoMedia($media);
                } elseif ($entity instanceof Promotion) {
                    $entity->setImageMedia($media);
                } elseif ($entity instanceof Evenement) {
                    $entity->setImageMedia($media);
                }
                $associated++;
            }
        }

        $this->entityManager->flush();
        $output->writeln(sprintf('<info>%d images associées.</info>', $associated));

        return Command::SUCCESS;
    }

    private function findEntity(string $class, string $slug): ?object
    {
        if (in_array($class, [Boutique::class, Restaurant::class], true)) {
            return $this->entityManager->getRepository($class)
                ->createQueryBuilder('entity')
                ->join('entity.enseigne', 'enseigne')
                ->andWhere('enseigne.slug = :slug')
                ->setParameter('slug', $slug)
                ->getQuery()
                ->getOneOrNullResult();
        }

        return $this->entityManager->getRepository($class)->findOneBy(['slug' => $slug]);
    }
}
