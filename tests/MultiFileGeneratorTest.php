<?php

use MartinLindhe\VueInternationalizationGenerator\Generator;


class MultiFileGeneratorTest extends \Orchestra\Testbench\TestCase
{
    private $config = [];

    protected function getEnvironmentSetUp($app)
    {
        $app->setBasePath(realpath(__DIR__ . '/..'));
    }

    private function genOutDir()
    {
        return '/tests/output/' . sha1(microtime(true) . mt_rand()) . '/';
    }

    private function destroyTempFiles($dir)
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($dir);
    }

    private function evaluateMultiOutput($input, $expected, $format = 'es6', $multiLocales = false)
    {
        $this->config['jsPath'] = $this->genOutDir();
        $outDir = base_path() . $this->config['jsPath'];
        $this->config['langPath'] = '/tests/input/' . $input;

        $out = (new Generator($this->config))->generateMultiple(__DIR__ . '/input/' . $input, 'es6', $multiLocales);
        $this->config = [];

        $expected = new RecursiveDirectoryIterator(base_path() . '/tests/result/' . $expected, FilesystemIterator::SKIP_DOTS);

        $expectedFiles = [];

        foreach($expected as $path => $file) {
            $resultFile = $outDir . $file->getFilename();
            $expectedFiles[] = $resultFile . PHP_EOL;
            $this->assertEquals(
                file_get_contents($path),
                file_get_contents($resultFile),
                "File $resultFile doesn't match expected $path."
            );
        }
        asort($expectedFiles); // RDI is unordered
        $this->assertEquals(implode($expectedFiles), $out);

        $this->destroyTempFiles($outDir);
        $this->config = [];
    }

    public function testBasic()
    {
        $this->evaluateMultiOutput('basic', 'basic');
    }

    public function testBasicMulti()
    {
        // FIXME code skips json files
        $this->evaluateMultiOutput('multiple', 'multi_file');
    }

    public function testMultiLocale()
    {
        // FIXME code skips json files
        $this->evaluateMultiOutput('multiple', 'multi_locale', 'es6', true);
    }
}