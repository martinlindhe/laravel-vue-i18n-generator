<?php namespace MartinLindhe\VueInternationalizationGenerator;

use DirectoryIterator;
use Exception;
use App;

class Generator
{

    private $availableLocales = [];
    private $filesToCreate = [];

    const VUEX_I18N = 'vuex-i18n';
    const VUE_I18N = 'vue-i18n';

    private $i18nLib;

    /**
     * The constructor
     *
     * @param string $i18nLib
     */
    public function __construct($i18nLib = self::VUE_I18N)
    {
        $this->i18nLib = $i18nLib;
    }

    /**
     * @param string $path
     * @param boolean $umd
     * @param boolean $withVendor
     * @return string
     * @throws Exception
     */
    public function generateFromPath($path, $umd = null, $withVendor = false)
    {
        if (!is_dir($path)) {
            throw new Exception('Directory not found: ' . $path);
        }

        $locales = [];
        $dir = new DirectoryIterator($path);
        $jsBody = '';
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if(!$withVendor && in_array($fileinfo->getFilename(), ['vendor'])) {
                    continue;
                }

                $noExt = $this->removeExtension($fileinfo->getFilename());

                if ($fileinfo->isDir()) {
                    $local = $this->allocateLocaleArray($fileinfo->getRealPath());
                } else {
                    $local = $this->allocateLocaleJSON($fileinfo->getRealPath());
                    if ($local === null) continue;
                }

                if (isset($locales[$noExt])) {
                    $locales[$noExt] = array_merge($local, $locales[$noExt]);
                } else {
                    $locales[$noExt] = $local;
                }
            }
        }

        $locales = $this->adjustVendor($locales);

        $jsonLocales = json_encode($locales, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

        if (!$umd) {
            $jsBody = $this->getES6Module($jsonLocales);
        } else {
            $jsBody = $this->getUMDModule($jsonLocales);
        }
        return $jsBody;
    }

    /**
     * @param string $path
     * @param boolean $umd
     * @return string
     * @throws Exception
     */
    public function generateMultiple($path, $umd = null)
    {
        if (!is_dir($path)) {
            throw new Exception('Directory not found: ' . $path);
        }
        $jsPath = base_path() . config('vue-i18n-generator.jsPath');
        $locales = [];
        $fileToCreate = '';
        $createdFiles = '';
        $dir = new DirectoryIterator($path);
        $jsBody = '';
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()
                && !in_array($fileinfo->getFilename(), ['vendor'])
            ) {
                $noExt = $this->removeExtension($fileinfo->getFilename());
                if (!in_array($noExt, $this->availableLocales)) {
                    App::setLocale($noExt);
                    $this->availableLocales[] = $noExt;
                }
                if ($fileinfo->isDir()) {
                    $local = $this->allocateLocaleArray($fileinfo->getRealPath());
                } else {
                    $local = $this->allocateLocaleJSON($fileinfo->getRealPath());
                    if ($local === null) continue;
                }

                if (isset($locales[$noExt])) {
                    $locales[$noExt] = array_merge($local, $locales[$noExt]);
                } else {
                    $locales[$noExt] = $local;
                }


            }
        }
        foreach ($this->filesToCreate as $fileName => $data) {
            $fileToCreate = $jsPath . $fileName . '.js';
            $createdFiles .= $fileToCreate . PHP_EOL;
            $jsonLocales = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

            if (!$umd) {
                $jsBody = $this->getES6Module($jsonLocales);
            } else {
                $jsBody = $this->getUMDModule($jsonLocales);
            }

            if (!is_dir(dirname($fileToCreate))) {
                mkdir(dirname($fileToCreate), 0777, true);
            }

            file_put_contents($fileToCreate, $jsBody);
        }
        return $createdFiles;
    }


    /**
     * @param string $path
     * @return array
     */
    private function allocateLocaleJSON($path)
    {
        // Ignore non *.json files (ex.: .gitignore, vim swap files etc.)
        if (pathinfo($path, PATHINFO_EXTENSION) !== 'json') {
            return null;
        }
        $tmp = (array)json_decode(file_get_contents($path), true);
        if (gettype($tmp) !== "array") {
            throw new Exception('Unexpected data while processing ' . $path);
        }

        return $this->adjustArray($tmp);
    }

    /**
     * @param string $path
     * @return array
     */
    private function allocateLocaleArray($path)
    {
        $data = [];
        $dir = new DirectoryIterator($path);
        $lastLocale = last($this->availableLocales);
        foreach ($dir as $fileinfo) {
            // Do not mess with dotfiles at all.
            if ($fileinfo->isDot()) {
                continue;
            }

            if ($fileinfo->isDir()) {
                // Recursivley iterate through subdirs, until everything is allocated.

                $data[$fileinfo->getFilename()] =
                    $this->allocateLocaleArray($path . '/' . $fileinfo->getFilename());
            } else {
                $noExt = $this->removeExtension($fileinfo->getFilename());
                $fileName = $path . '/' . $fileinfo->getFilename();

                // Ignore non *.php files (ex.: .gitignore, vim swap files etc.)
                if (pathinfo($fileName, PATHINFO_EXTENSION) !== 'php') {
                    continue;
                }


                $tmp = include($fileName);

                if (gettype($tmp) !== "array") {
                    throw new Exception('Unexpected data while processing ' . $fileName);
                    continue;
                }
                if($lastLocale !== false){
                    $root = realpath(base_path() . config('vue-i18n-generator.langPath') . '/' . $lastLocale);
                    $filePath = $this->removeExtension(str_replace('\\', '_', ltrim(str_replace($root, '', realpath($fileName)), '\\')));
                    $this->filesToCreate[$filePath][$lastLocale] = $this->adjustArray($tmp);
                }

                $data[$noExt] = $this->adjustArray($tmp);

            }
        }
        return $data;
    }

    /**
     * @param array $arr
     * @return array
     */
    private function adjustArray(array $arr)
    {
        $res = [];
        foreach ($arr as $key => $val) {

            if (is_string($val)) {
                $res[$key] = $this->adjustString($val);
            } else {
                $res[$key] = $this->adjustArray($val);
            }
        }
        return $res;
    }

    /**
     * Adjus vendor index placement.
     * 
     * @param array $locales
     * 
     * @return array
     */
    private function adjustVendor($locales)
    {
        if(isset($locales['vendor'])) {
            foreach($locales['vendor'] as $vendor => $data) {
                foreach($data as $key => $group) {
                    foreach($group as $locale => $lang) {
                        $locales[$locale]['vendor'][$vendor][$key] = $lang;
                    }
                }
            }

            unset($locales['vendor']);
        }

        return $locales;
    }

    /**
     * Turn Laravel style ":link" into vue-i18n style "{link}" and
     * turn Laravel style "|" into vuex-i18n style ":::" when using vuex-i18n.
     *
     * @param string $s
     * @return string
     */
    private function adjustString($s)
    {
        if (!is_string($s)) {
            return $s;
        }

        if ($this->i18nLib === self::VUEX_I18N) {
            $searchPipePattern = '/(\s)*(\|)(\s)*/';
            $threeColons = ' ::: ';

            $s = preg_replace($searchPipePattern, $threeColons, $s);
        }

        return preg_replace_callback(
            '/(?<!mailto|tel):\w+/',
            function ($matches) {
                return '{' . mb_substr($matches[0], 1) . '}';
            },
            $s
        );
    }

    /**
     * Returns filename, with extension stripped
     * @param string $filename
     * @return string
     */
    private function removeExtension($filename)
    {
        $pos = mb_strrpos($filename, '.');
        if ($pos === false) {
            return $filename;
        }

        return mb_substr($filename, 0, $pos);
    }

    /**
     * Returns an UMD style module
     * @param string $body
     * @return string
     */
    private function getUMDModule($body)
    {
        $js = <<<HEREDOC
(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
        typeof define === 'function' && define.amd ? define(factory) :
            (global.vuei18nLocales = factory());
}(this, (function () { 'use strict';
    return {$body}
})));
HEREDOC;
        return $js;
    }

    /**
     * Returns an ES6 style module
     * @param string $body
     * @return string
     */
    private function getES6Module($body)
    {
        return "export default {$body}";
    }
}