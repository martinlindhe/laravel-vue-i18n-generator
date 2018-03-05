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
            (new Generator([]))->generateFromPath($root));
        $this->destroyLocaleFilesFrom($arr, $root);
    }

    function testBasicWithVendor()
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
            ],
            'vendor' => [
                'test-vendor' => [
                    'en' => [
                        'test-lang' => [
                            'maybe' => 'maybe'
                        ]
                    ],
                    'sv' => [
                        'test-lang' => [
                            'maybe' => 'kanske'
                        ]
                    ]
                ]
            ],
        ];

        $root = $this->generateLocaleFilesFrom($arr);

        $this->assertEquals(
            'export default {' . PHP_EOL
            . '    "en": {' . PHP_EOL
            . '        "help": {' . PHP_EOL
            . '            "yes": "yes",' . PHP_EOL
            . '            "no": "no"' . PHP_EOL
            . '        },' . PHP_EOL
            . '        "vendor": {' . PHP_EOL
            . '            "test-vendor": {' . PHP_EOL
            . '                "test-lang": {' . PHP_EOL
            . '                    "maybe": "maybe"' . PHP_EOL
            . '                }' . PHP_EOL
            . '            }' . PHP_EOL
            . '        }' . PHP_EOL
            . '    },' . PHP_EOL
            . '    "sv": {' . PHP_EOL
            . '        "help": {' . PHP_EOL
            . '            "yes": "ja",' . PHP_EOL
            . '            "no": "nej"' . PHP_EOL
            . '        },' . PHP_EOL
            . '        "vendor": {' . PHP_EOL
            . '            "test-vendor": {' . PHP_EOL
            . '                "test-lang": {' . PHP_EOL
            . '                    "maybe": "kanske"' . PHP_EOL
            . '                }' . PHP_EOL
            . '            }' . PHP_EOL
            . '        }' . PHP_EOL
            . '    }' . PHP_EOL
            . '}' . PHP_EOL,
            (new Generator([]))->generateFromPath($root, false, true));

        $this->destroyLocaleFilesFrom($arr, $root);
    }

    function testBasicWithVuexLib()
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
            (new Generator([]))->generateFromPath($root));

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
            (new Generator([]))->generateFromPath($root));

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
            (new Generator([]))->generateFromPath($root));

        $this->destroyLocaleFilesFrom($arr, $root);
    }

    function testPluralization()
    {
        $arr = [
            'en' => [
                'plural' => [
                    'one' => 'There is one apple|There are many apples',
                    'two' => 'There is one apple | There are many apples',
                    'five' => [
                        'three' => 'There is one apple    | There are many apples',
                        'four' => 'There is one apple |     There are many apples',
                    ]
                ]
            ]
        ];

        $root = $this->generateLocaleFilesFrom($arr);

        // vue-i18n
        $this->assertEquals(
            'export default {' . PHP_EOL
            . '    "en": {' . PHP_EOL
            . '        "plural": {' . PHP_EOL
            . '            "one": "There is one apple|There are many apples",' . PHP_EOL
            . '            "two": "There is one apple | There are many apples",' . PHP_EOL
            . '            "five": {' . PHP_EOL
            . '                "three": "There is one apple    | There are many apples",' . PHP_EOL
            . '                "four": "There is one apple |     There are many apples"' . PHP_EOL
            . '            }' . PHP_EOL
            . '        }' . PHP_EOL
            . '    }' . PHP_EOL
            . '}' . PHP_EOL,
            (new Generator(['i18nLib' => 'vue-i18n']))->generateFromPath($root));

        // vuex-i18n
        $this->assertEquals(
            'export default {' . PHP_EOL
            . '    "en": {' . PHP_EOL
            . '        "plural": {' . PHP_EOL
            . '            "one": "There is one apple ::: There are many apples",' . PHP_EOL
            . '            "two": "There is one apple ::: There are many apples",' . PHP_EOL
            . '            "five": {' . PHP_EOL
            . '                "three": "There is one apple ::: There are many apples",' . PHP_EOL
            . '                "four": "There is one apple ::: There are many apples"' . PHP_EOL
            . '            }' . PHP_EOL
            . '        }' . PHP_EOL
            . '    }' . PHP_EOL
            . '}' . PHP_EOL,
            (new Generator(['i18nLib' => 'vuex-i18n']))->generateFromPath($root));

        $this->destroyLocaleFilesFrom($arr, $root);
    }
}
