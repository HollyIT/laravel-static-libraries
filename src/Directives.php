<?php

namespace HollyIT\StaticLibraries;

class Directives
{
    public static function renderStyleSheets($expression = ''): string
    {
        return '<?php foreach(app(\HollyIT\StaticLibraries\LibrariesManager::class)->getStyleSheets() as $sheet){
                echo $sheet . PHP_EOL . "\t\t";
            } ?>';
    }

    public static function renderFooter($expression = ''): string
    {
        return '<?php echo app(\HollyIT\StaticLibraries\LibrariesManager::class)->renderFooter() ?>';
    }

    public static function renderHead($expression = ''): string
    {
        return '<?php echo app(\HollyIT\StaticLibraries\LibrariesManager::class)->renderHead() ?>';
    }
}
