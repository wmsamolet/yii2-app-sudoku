<?php

namespace wmsamolet\yii2\tools\helpers;

class ObjectFinderHelper
{
    /**
     * @return string[]
     */
    public static function findClassesByPath(string $directoryPath, string $term = null): array
    {
        $result = [];
        $realPath = realpath($directoryPath);
        $filePaths = FileHelper::rsearch($realPath, '/^.+?[\/\\][A-Z][\w\.]+\.php$/u');

        foreach ($filePaths as $filePath) {
            $namespace = $className = null;

            if (($fileRealPath = realpath($filePath)) === false) {
                continue;
            }

            $fp = fopen($fileRealPath, 'rb');

            for ($i = 0, $max = 50; $i < $max && !feof($fp); $i++) {
                $line = fgets($fp);

                if (
                    $namespace === null
                    &&
                    stripos($line, 'namespace') !== false
                    &&
                    preg_match('/namespace\s+([^\s;]+)/', $line, $matches)
                ) {
                    $namespace = $matches[1];

                    continue;
                }

                /** @noinspection NotOptimalIfConditionsInspection */
                if (
                    (
                        strpos($line, 'class') === 0
                        ||
                        strpos($line, 'interface') === 0
                    )
                    &&
                    preg_match('/^(class|interface)\s+([a-z]\w+)/ui', $line, $matches)
                ) {
                    $className = trim($matches[2]);

                    break;
                }
            }

            if ($className === null) {
                continue;
            }

            $result[(string)$fileRealPath] = $namespace . '\\' . $className;
        }

        if ($term !== null) {
            $result = array_filter(
                $result,
                function ($foundPath) use ($term) {
                    return stripos($foundPath, $term) !== false;
                }
            );
        }

        return $result;
    }

    /**
     * @param string $filePath
     *
     * @return string|null
     */
    public static function classNamespaceFromFile(string $filePath): ?string
    {
        $fileNamespace = null;
        $fileClassName = null;
        $handle = fopen($filePath, 'rb');

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (!$fileNamespace && strpos($line, 'namespace') === 0) {
                    $parts = explode(' ', $line);
                    $fileNamespace = rtrim(trim($parts[1]), ';');
                }

                if (strpos($line, 'class') === 0) {
                    $parts = explode(' ', $line);
                    $fileClassName = rtrim(trim($parts[1]), ';');
                    break;
                }
            }

            fclose($handle);
        }

        $className = $fileNamespace && $fileClassName ? $fileNamespace . '\\' . $fileClassName : null;

        return $className && class_exists($className) ? $className : null;
    }
}