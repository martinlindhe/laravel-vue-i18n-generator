<?php namespace MartinLindhe\VueInternationalizationGenerator;

use DirectoryIterator;
use Exception;

class Generator
{
    /**
     * @param string $path
     * @return string
     * @throws Exception
     */
    public function generateFromPath($path)
    {
        if (!is_dir($path)) {
            throw new Exception('Directory not found: '.$path);
        }

        $locales = [];
        $dir = new DirectoryIterator($path);

        // directories
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()
                && $fileinfo->isDir()
                && !in_array($fileinfo->getFilename(), ['vendor'])
            ) {
                $locales[$fileinfo->getFilename()] =
                    $this->allocateLocaleArray($path . '/' . $fileinfo->getFilename());
            }
        }

        // json files
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()
                && !$fileinfo->isDir()
                && !in_array($fileinfo->getFilename(), ['vendor'])
            ) {
                $noExt = $this->removeExtension($fileinfo->getFilename());
                $fileName = $path . '/' . $fileinfo->getFilename();

                // Ignore non *.json files (ex.: .gitignore, vim swap files etc.)
                if (pathinfo($fileName, PATHINFO_EXTENSION) !== 'json') {
                    continue;
                }
                $tmp = json_decode(file_get_contents($fileinfo->getRealPath()));
                if (gettype($tmp) !== "object") {
                    throw new Exception('Unexpected data while processing '.$fileName);
                    continue;
                }

                if (isset($locales[$noExt])) {
                    $locales[$noExt] = array_merge($tmp, $locales[$noExt]);
                } else {
                    $locales[$noExt] = $tmp;
                }
            }
        }

        return 'export default '
            . json_encode($locales, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    /**
     * @param string $path
     * @return array
     */
    private function allocateLocaleArray($path)
    {
        $data = [];

        $dir = new DirectoryIterator($path);
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
                    throw new Exception('Unexpected data while processing '.$fileName);
                    continue;
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
     * Turn Laravel style ":link" into vue-i18n style "{link}"
     * @param string $s
     * @return string
     */
    private function adjustString($s)
    {
        if (!is_string($s)) {
            return $s;
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
}
