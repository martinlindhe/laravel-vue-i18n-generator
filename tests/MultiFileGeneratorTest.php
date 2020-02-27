<?php

use MartinLindhe\VueInternationalizationGenerator\Generator;

class MultiFileGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $config = [];

    private function evaluateMultiOutput($input, $expected, $format = 'es6', $withVendor = false)
    {
        $this->assertEquals(
            file_get_contents(__DIR__ . '/result/' . $expected),
            (new Generator($this->config))->generateMultiple(__DIR__ . '/input/' . $input, $format, $withVendor));

        $this->config = [];
    }

    public function testBasic()
    {
        $input = 'basic';
        $out = (new Generator($this->config))->generateMultiple(__DIR__ . '/input/' . $input);
        dd($out);
    }
}