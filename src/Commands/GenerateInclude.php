<?php namespace MartinLindhe\VueInternationalizationGenerator\Commands;

use DirectoryIterator;
use Exception;
use Illuminate\Console\Command;

class GenerateInclude extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-i18n:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generates a vue-i18n compatible js array out of project translations";


    /**
     * Execute the console command.
     * @return mixed
     * @throws Exception
     */
    public function handle()
    {
        $root = base_path() . '/resources/lang';
        if (!is_dir($root)) {
            throw new Exception('Path not found: '.$root);
        }

        $projectLocales = [];
        $dir = new DirectoryIterator($root);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()
                && $fileinfo->isDir()
                && !in_array($fileinfo->getFilename(), ['vendor'])
            ) {
                $projectLocales[$fileinfo->getFilename()] =
                    $this->allocateLocaleArray($root . '/' . $fileinfo->getFilename());
            }
        }

        $data = 'export default '
            . json_encode($projectLocales, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

        $jsFile = base_path() . '/resources/assets/js/vue-i18n-locales.generated.js';

        file_put_contents($jsFile, $data);

        echo "Written to " . $jsFile . PHP_EOL;
    }

    private function allocateLocaleArray($path)
    {
        $data = [];

        $dir = new DirectoryIterator($path);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $noExt = $this->removeExtension($fileinfo->getFilename());
                $data[$noExt] = include($path . '/' . $fileinfo->getFilename());
            }
        }

        return $data;
    }

    private function removeExtension($filename)
    {
        $pos = mb_strrpos($filename, '.');
        if ($pos === false) {
            return $filename;
        }

        return mb_substr($filename, 0, $pos);
    }
}
