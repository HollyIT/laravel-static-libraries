<?php

namespace HollyIT\StaticLibraries\Commands;

use HollyIT\StaticLibraries\Commands\Concerns\ParsesLibrariesArgument;
use HollyIT\StaticLibraries\Contracts\PublishesStaticAssets;
use HollyIT\StaticLibraries\Contracts\StaticAssetsDriver;
use HollyIT\StaticLibraries\LibrariesManager;
use Illuminate\Console\Command;

class ListStaticAssetsCommand extends Command
{
    use ParsesLibrariesArgument;

    protected $signature = 'static-libraries:list';

    protected $description = 'Copies static files to a location for serving directly from a web server, based upon your storage driver.';

    protected LibrariesManager $librariesManager;

    protected StaticAssetsDriver $driver;

    public function __construct(LibrariesManager $librariesManager)
    {
        parent::__construct();
        $this->librariesManager = $librariesManager;
        $this->driver = $this->librariesManager->driver();
    }

    public function handle(): int
    {
        $info = $this->librariesManager->devInfo();
        $isPublishable = $this->librariesManager->driver() instanceof PublishesStaticAssets;
        $counts = [
            'libraries' => 0,
            'assets' => 0,
            'unpublished' => 0,
            'stale' => 0,
        ];

        foreach ($info as $library) {
            $counts['libraries']++;
            $this->output->writeln('<fg=green;options=bold>Library: <options=bold>'.$library['name'].'</>');
            if (! empty($library['depends_on'])) {
                $this->outputInfo('Depends on', $library['depends_on']);
            }
            $this->outputInfo('Base path:', $library['base_path']);
            $this->components->twoColumnDetail('  ok');
            $this->components->twoColumnDetail('  <fg=green;options=bold>Base path</>');
            if (! empty($library['required_before'])) {
                $this->outputInfo('Is required before', $library['required_before']);
            }

            if (! empty($library['required_after'])) {
                $this->outputInfo('Is required after', $library['depends_on']);
            }

            $this->output->writeln('<fg=green;options=bold>Assets:</>');
            foreach ($library['assets'] as $asset) {
                $counts['assets']++;
                $lines = [];
                $lines[] = ['Type', $asset['type']];
                $lines[] = ['File', $asset['file']];
                $lines[] = ['URL', $asset['url']];
                if ($isPublishable) {
                    if (! $asset['is_published']) {
                        $counts['unpublished']++;
                    }

                    if ($asset['is_stale']) {
                        $counts['stale']++;
                    }
                    $lines[] = ['Is published', $this->formatBoolean($asset['is_published'])];
                    $lines[] = ['Is stale', $this->formatBoolean($asset['is_stale'])];
                }

                $this->output->table(['Item', 'Value'], $lines);
                $this->output->writeln('');
            }
            $this->output->writeln('');
        }

        $this->output->writeln('Summary:');
        foreach ($counts as $label => $value) {
            $this->outputInfo($label, $value);
        }

        if ($counts['stale']) {
            $this->warn('You have stale libraries, which could result in problems. Please execute: php artisan static-libraries:publish');
        }

        return 0;
    }

    protected function formatBoolean($value): string
    {
        if ($value) {
            return '<fg=green>True</>';
        } else {
            return '<fg=red>False</>';
        }
    }

    protected function outputInfo($label, $value)
    {
        $value = is_array($value) ? implode(', ', $value) : $value;
        $this->components->twoColumnDetail($label, $value);
    }
}
