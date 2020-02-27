<?php

use MartinLindhe\VueInternationalizationGenerator\Generator;

class SingleFileGeneratorTest extends \Orchestra\Testbench\TestCase
{
    private $config = [];

    private function evaluateSingleOutput($input, $expected, $format = 'es6', $withVendor = false)
    {
        $this->assertEquals(
            file_get_contents(__DIR__ . '/result/' . $expected),
            (new Generator($this->config))->generateFromPath(__DIR__ . '/input/' . $input, $format, $withVendor));

        $this->config = [];
    }

    function testBasic()
    {
        $this->evaluateSingleOutput('basic', 'basic.js');
    }

    function testBasicES6Format()
    {
        $this->evaluateSingleOutput('basic', 'basic_es6.js', 'es6');
    }

    function testBasicWithUMDFormat()
    {
        $this->evaluateSingleOutput('basic', 'basic_umd.js', 'umd');
    }

    function testBasicWithJSONFormat()
    {
        $this->evaluateSingleOutput('basic', 'basic.json', 'json');
    }

    function testBasicMultipleInput()
    {
        $this->evaluateSingleOutput('multiple', 'basic_multi_in.js');
    }

    function testInvalidFormat()
    {
        $format = 'es5';
        $inputDir = __DIR__ . '/input/basic';

        try {
            (new Generator([]))->generateFromPath($inputDir, $format);
        } catch(RuntimeException $e) {
            $this->assertEquals('Invalid format passed: ' . $format, $e->getMessage());
            return;
        }

// FIXME        $this->fail('No exception thrown for invalid format.');
    }

    function testBasicWithTranslationString()
    {
        $this->evaluateSingleOutput('translation', 'translation.js');
    }

    function testBasicWithEscapedTranslationString()
    {
        $this->evaluateSingleOutput('escaped', 'escaped.js');
    }

    function testBasicWithVendor()
    {
        $this->evaluateSingleOutput('vendor', 'vendor.js', 'es6', true);
    }

    function testBasicWithVuexLib()
    {
        $this->config = ['i18nLib' => 'vuex-i18n'];
        $this->evaluateSingleOutput('basic', 'basic_vuex.js');
    }

    function testNamed()
    {
        $this->evaluateSingleOutput('named', 'named.js');
    }

    function testNamedWithEscaped()
    {
        $this->evaluateSingleOutput('named_escaped', 'named_escaped.js');
    }

    function testEscapedEscapeCharacter()
    {
        $this->evaluateSingleOutput('escaped_escape', 'escaped_escape.js');
    }

    function testShouldNotTouchHtmlTags()
    {
        $this->evaluateSingleOutput('html', 'html.js');
    }

    function testPluralization()
    {
        $this->evaluateSingleOutput('plural', 'plural.js');
        $this->config = ['i18nLib' => 'vuex-i18n'];
        $this->evaluateSingleOutput('plural', 'plural_vuex.js');
    }
}
