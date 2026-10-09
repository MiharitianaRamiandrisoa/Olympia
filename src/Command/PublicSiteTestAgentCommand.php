<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:test:public-site',
    description: 'Agent de smoke-test des principales pages publiques Olympia.'
)]
final class PublicSiteTestAgentCommand extends Command
{
    private const PAGES = [
        '/' => 'Olympia',
        '/enseignes' => 'Enseignes',
        '/restaurants' => 'Restaurants',
        '/promotions' => 'Promotions',
        '/evenements' => 'Événements',
        '/services' => 'Services',
        '/contact' => 'Contact',
    ];

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('base-url', null, InputOption::VALUE_REQUIRED, 'URL du site à tester', 'http://127.0.0.1:8000');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseUrl = rtrim((string) $input->getOption('base-url'), '/');
        $failed = 0;

        foreach (self::PAGES as $path => $expectedText) {
            $url = $baseUrl.$path;
            try {
                $response = $this->httpClient->request('GET', $url, ['timeout' => 10]);
                $status = $response->getStatusCode();
                $body = $response->getContent(false);
                $ok = $status >= 200 && $status < 300 && str_contains($body, $expectedText);
            } catch (\Throwable $exception) {
                $status = 'ERR';
                $ok = false;
            }

            $output->writeln(sprintf('%s %s %s', $ok ? '<info>OK</info>' : '<error>KO</error>', $status, $url));
            if (!$ok) {
                ++$failed;
            }
        }

        $output->writeln($failed === 0
            ? '<info>Agent terminé : toutes les pages publiques répondent correctement.</info>'
            : sprintf('<error>Agent terminé : %d page(s) en échec.</error>', $failed));

        return $failed === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
