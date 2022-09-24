<?php

namespace HollyIT\StaticLibraries\Commands;

use HollyIT\StaticLibraries\Commands\Concerns\ParsesLibrariesArgument;
use HollyIT\StaticLibraries\Contracts\PublishesStaticAssets;
use HollyIT\StaticLibraries\Contracts\StaticAssetsDriver;
use HollyIT\StaticLibraries\LibrariesManager;
use Illuminate\Console\Command;

class PublishStaticAssetsCommand extends Command
{
    use ParsesLibrariesArgument;

    protected $signature = 'static-libraries:publish
                            {library? : The name of the library to publish. You can pass multiple libraries by separating them with a comma}
                            {--F|force : Will force assets to publish regardless of their cached file modified time. }
                            {--C|clean : Will force assets to publish regardless of their cached file modified time. }';

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
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (! ($this->driver instanceof PublishesStaticAssets)) {
            $this->output->error('The current driver does not support publishing');

            return static::FAILURE;
        }

        $completed = [];

        foreach ($this->getLibrariesFromInput() as $library) {
            $this->driver->publish($library, $this->option('force'));
            $completed[] = $library->getName();
        }

        $this->output->success('Published packages: '.implode(', ', $completed));

        return static::SUCCESS;
    }
}
