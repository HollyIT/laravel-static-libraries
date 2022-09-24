<?php

namespace HollyIT\StaticLibraries\Commands;

use HollyIT\StaticLibraries\Commands\Concerns\ParsesLibrariesArgument;
use HollyIT\StaticLibraries\Contracts\PublishesStaticAssets;
use HollyIT\StaticLibraries\Contracts\StaticAssetsDriver;
use HollyIT\StaticLibraries\LibrariesManager;
use Illuminate\Console\Command;

class UnpublishStaticAssetsCommand extends Command
{
    use ParsesLibrariesArgument;

    protected $signature = 'static-libraries:unpublish
                            {library? : The name of the library to publish. You may pass "all" to publish all libraries}';

    protected $description = 'Deletes the copied assets from your static server location.';

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
            $this->driver->unpublish($library);
            $completed[] = $library->getName();
        }

        $this->output->success('Unpublished packages '.implode(', ', $completed));

        return static::SUCCESS;
    }
}
