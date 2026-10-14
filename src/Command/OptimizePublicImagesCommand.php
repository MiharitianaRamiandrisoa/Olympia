<?php

namespace App\Command;

use App\Service\ImageOptimizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'app:images:optimize', description: 'Génère les variantes WebP des images statiques du site.')]
final class OptimizePublicImagesCommand extends Command
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly ImageOptimizer $imageOptimizer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Regénère les variantes même si elles sont à jour.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $imagesDirectory = $this->kernel->getProjectDir().'/public/images';
        if (!is_dir($imagesDirectory)) {
            $output->writeln('<error>Le dossier public/images est introuvable.</error>');
            return Command::FAILURE;
        }

        $force = (bool) $input->getOption('force');
        $optimized = 0;
        $skipped = 0;
        $savedBytes = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($imagesDirectory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                continue;
            }

            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($this->kernel->getProjectDir().'/public/')));
            if (str_starts_with($relativePath, 'images/optimized/')) {
                continue;
            }
            if (!in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'], true)) {
                continue;
            }

            $result = $this->imageOptimizer->optimizeStaticImage($relativePath, $force);
            if ($result['skipped']) {
                ++$skipped;
                continue;
            }

            ++$optimized;
            $savedBytes += max(0, $result['sourceBytes'] - $result['masterBytes']);
            $output->writeln(sprintf('<info>Optimisée :</info> %s (%s → %s)', $relativePath, $this->formatBytes($result['sourceBytes']), $this->formatBytes($result['masterBytes'])));
        }

        $output->writeln(sprintf('<info>Terminé :</info> %d image(s) optimisée(s), %d déjà à jour, %s économisés sur les versions principales.', $optimized, $skipped, $this->formatBytes($savedBytes)));
        return Command::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' octets';
        }
        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1, ',', ' ').' Ko';
        }

        return number_format($bytes / 1024 / 1024, 2, ',', ' ').' Mo';
    }
}
