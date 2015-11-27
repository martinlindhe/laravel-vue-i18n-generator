<?php

use MartinLindhe\VueInternationalizationGenerator\Generator;

class GenerateTest extends \PHPUnit_Framework_TestCase
{
    private function generateLocaleFilesFrom(array $arr)
    {
        $root = sys_get_temp_dir() . '/' . sha1(microtime(true) . mt_rand());

        if (!is_dir($root)) {
            mkdir($root, 0777, true);
        }

        foreach ($arr as $key => $val) {

            if (!is_dir($root . '/' . $key)) {
                mkdir($root . '/' . $key);
            }

            foreach ($val as $group => $content) {
                $outFile = $root . '/'. $key . '/' . $group . '.php';
                file_put_contents($outFile, '<?php return ' . var_export($content, true) . ';');
            }
        }

        return $root;
    }

    private function destroyLocaleFilesFrom(array $arr, $root)
    {
        foreach ($arr as $key => $val) {

            foreach ($val as $group => $content) {
                $outFile = $root . '/'. $key . '/' . $group . '.php';
                if (file_exists($outFile)) {
                    unlink($outFile);
                }
            }

            if (is_dir($root . '/' . $key)) {
                rmdir($root . '/' . $key);
            }

        }

        if (is_dir($root)) {
            rmdir($root);
        }
    }

    function testBasic()
    {
        $arr = [
            'en' => [
                'help' => [
                    'yes' => 'yes',
                    'no' => 'no',
                ]
            ],
            'sv' => [
                'help' => [
                    'yes' => 'ja',
                    'no' => 'nej',
                ]
            ]
        ];

        $root = $this->generateLocaleFilesFrom($arr);

        $this->assertEquals(
            'export default {' . PHP_EOL
            . '    "en": {' . PHP_EOL
            . '        "help": {' . PHP_EOL
            . '            "yes": "yes",' . PHP_EOL
            . '            "no": "no"' . PHP_EOL
            . '        }' . PHP_EOL
            . '    },' . PHP_EOL
            . '    "sv": {' . PHP_EOL
            . '        "help": {' . PHP_EOL
            . '            "yes": "ja",' . PHP_EOL
            . '            "no": "nej"' . PHP_EOL
            . '        }' . PHP_EOL
            . '    }' . PHP_EOL
            . '}' . PHP_EOL,
            (new Generator)->generateFromPath($root));

        $this->destroyLocaleFilesFrom($arr, $root);
    }

    function testNamed()
    {
        $arr = [
            'en' => [
                'help' => [
                    'yes' => 'see :link y :lonk',
                    'no' => [
                        'one' => 'see :link',
                    ]
                ]
            ]
        ];

        $root = $this->generateLocaleFilesFrom($arr);

        $this->assertEquals(
            'export default {' . PHP_EOL
            . '    "en": {' . PHP_EOL
            . '        "help": {' . PHP_EOL
            . '            "yes": "see {link} y {lonk}",' . PHP_EOL
            . '            "no": {' . PHP_EOL
            . '                "one": "see {link}"' . PHP_EOL
            . '            }' . PHP_EOL
            . '        }' . PHP_EOL
            . '    }' . PHP_EOL
            . '}' . PHP_EOL,
            (new Generator)->generateFromPath($root));

        $this->destroyLocaleFilesFrom($arr, $root);
    }

    function testShouldNotTouchHtmlTags()
    {
        $arr = [
            'en' => [
                'help' => [
                    'yes' => 'see <a href="mailto:mail@com">',
                    'no' => 'see <a href=":link">',
                ]
            ]
        ];

        $root = $this->generateLocaleFilesFrom($arr);

        $this->assertEquals(
            'export default {' . PHP_EOL
            . '    "en": {' . PHP_EOL
            . '        "help": {' . PHP_EOL
            . '            "yes": "see <a href=\"mailto:mail@com\">",' . PHP_EOL
            . '            "no": "see <a href=\"{link}\">"' . PHP_EOL
            . '        }' . PHP_EOL
            . '    }' . PHP_EOL
            . '}' . PHP_EOL,
            (new Generator)->generateFromPath($root));

        $this->destroyLocaleFilesFrom($arr, $root);
    }
}
