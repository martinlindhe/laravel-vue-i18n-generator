<?php namespace MartinLindhe\VueInternationalizationGenerator\Commands;

use Illuminate\Console\Command;

use MartinLindhe\VueInternationalizationGenerator\Generator;

class GenerateInclude extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue-i18n:generate {--umd} {--multi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generates a vue-i18n|vuex-i18n compatible js array out of project translations";

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        $root = base_path() . config('vue-i18n-generator.langPath');

        $umd = $this->option('umd');

        $multipleFiles = $this->option('multi');

        $i18nLib = config('vue-i18n-generator.i18nLib');

        if ($multipleFiles) {
            $files = (new Generator($i18nLib))
                ->generateMultiple($root, $umd);
            echo "Written to :" . PHP_EOL . $files . PHP_EOL;
            exit();
        }

        $data = (new Generator($i18nLib))
            ->generateFromPath($root, $umd);



        $jsFile = base_path() . config('vue-i18n-generator.jsFile');
        file_put_contents($jsFile, $data);

        echo "Written to " . $jsFile . PHP_EOL;
    }
}
