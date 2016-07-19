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
     */
    public function handle()
    {
        $root = base_path() . config('vue-i18n-generator.langPath');

        $data = (new Generator)
            ->generateFromPath($root);

        $jsFile = base_path() . config('vue-i18n-generator.jsFile');

        file_put_contents($jsFile, $data);

        echo "Written to " . $jsFile . PHP_EOL;
    }
}
