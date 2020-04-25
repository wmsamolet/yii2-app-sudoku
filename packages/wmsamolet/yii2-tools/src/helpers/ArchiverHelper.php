<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace wmsamolet\yii2\tools\helpers;

use wmsamolet\yii2\tools\exceptions\ArchiverPackException;
use wmsamolet\yii2\tools\exceptions\ArchiverUnpackException;
use PharData;
use Throwable;
use yii\helpers\FileHelper;

class ArchiverHelper
{
    public static function packTar(
        array $filePaths,
        string $packedFilePath,
        bool $unlink = false,
        bool $throwException = true
    ): ?string {
        try {
            if (!count($filePaths)) {
                throw new ArchiverPackException('Invalid param $filePaths, array is empty');
            }

            $packedFilePathTemp = $packedFilePath . '.temp.tar';

            if (file_exists($packedFilePathTemp)) {
                unlink($packedFilePathTemp);
            }

            $packedFilePathTempDir = dirname($packedFilePathTemp);

            if (!file_exists($packedFilePathTempDir)) {
                FileHelper::createDirectory($packedFilePathTempDir);
            }

            $phar = new PharData($packedFilePathTemp);

            foreach ($filePaths as $fileAlias => $filePath) {
                if (!file_exists($filePath)) {
                    throw new ArchiverPackException('Pack file not found: ' . $filePath);
                }

                if (is_int($fileAlias)) {
                    $fileAlias = basename($filePath);
                }

                $phar->addFile($filePath, $fileAlias);
            }

            $fp = fopen($packedFilePathTemp, 'rb');

            unset($phar);

            fclose($fp);

            if (!file_exists($packedFilePathTemp)) {
                throw new ArchiverPackException('Unknown error, temp packed file path not found: ' . $packedFilePathTemp);
            }

            rename($packedFilePathTemp, $packedFilePath);

            if (!file_exists($packedFilePath)) {
                throw new ArchiverPackException('Unknown error, packed file path not found: ' . $packedFilePath);
            }

            if ($unlink) {
                foreach ($filePaths as $filePath) {
                    unlink($filePath);
                }
            }
        } catch (Throwable $exception) {
            if ($throwException) {
                throw $exception;
            }

            return null;
        }

        return realpath($packedFilePath);
    }

    public static function unpackTar(
        string $packedFilePath,
        string $extractPath = null,
        bool $unlink = false,
        bool $throwException = true
    ): ?array {
        $result = [];

        try {
            if (!file_exists($packedFilePath)) {
                throw new ArchiverUnpackException('Packed file not found: ' . $packedFilePath);
            }

            $extractPath = $extractPath ?? dirname($packedFilePath);

            if (!file_exists($extractPath)) {
                FileHelper::createDirectory($extractPath);
            }

            $tar = new PharData($packedFilePath);

            /** @var \PharFileInfo $tarFile */
            foreach ($tar as $tarFile) {
                $result[] = realpath($extractPath) . DIRECTORY_SEPARATOR . $tarFile->getFilename();
            }

            $fp = fopen($packedFilePath, 'rb');

            $tar->extractTo($extractPath, null, true);

            unset($tar);

            fclose($fp);

            if (!count($result)) {
                throw new ArchiverUnpackException('Unknown error, empty unpack result');
            }

            if ($unlink) {
                unlink($packedFilePath);
            }
        } catch (Throwable $exception) {
            if ($throwException) {
                throw $exception;
            }

            return null;
        }

        return $result;
    }

    public static function packGzip(
        string $filePath,
        string $packedFilePath = null,
        bool $unlink = false,
        bool $throwException = true
    ): ?string {
        try {
            if (!file_exists($filePath)) {
                throw new ArchiverPackException('File not found: ' . $filePath);
            }

            $packedFilePath = $packedFilePath ?: $filePath . '.gz';
            $packedFilePathDir = dirname($packedFilePath);

            if (!file_exists($packedFilePathDir)) {
                FileHelper::createDirectory($packedFilePathDir);
            }

            $handle = fopen($filePath, 'rb');
            $handleGzip = gzopen($packedFilePath, 'wb9');

            while (!feof($handle) && ($dataPart = fread($handle, 4096)) !== false) {
                gzwrite($handleGzip, $dataPart, 4096);
            }

            gzclose($handleGzip);
            fclose($handle);

            if (!file_exists($packedFilePath)) {
                throw new ArchiverPackException('Packed file not found: ' . $packedFilePath);
            }

            if ($unlink) {
                unlink($filePath);
            }
        } catch (Throwable $exception) {
            if ($throwException) {
                throw $exception;
            }

            return null;
        }

        return realpath($packedFilePath);
    }

    public static function unpackGzip(
        string $packedFilePath,
        string $unpackedFilePath = null,
        bool $unlink = false,
        bool $throwException = true
    ): ?string {
        try {
            if (!file_exists($packedFilePath)) {
                throw new ArchiverUnpackException('Packed file not found: ' . $packedFilePath);
            }

            if ($unpackedFilePath === null) {
                $unpackedFilePath = (string)preg_replace('/\.gz$/i', '', $packedFilePath);
            }

            $unpackedFilePathDir = dirname($unpackedFilePath);

            if (!file_exists($unpackedFilePathDir)) {
                FileHelper::createDirectory($unpackedFilePathDir);
            }

            $handle = fopen($unpackedFilePath, 'wb+');
            $handleGzip = gzopen($packedFilePath, 'rb9');

            while (!feof($handleGzip) && ($dataPart = gzread($handleGzip, 4096)) !== false) {
                fwrite($handle, $dataPart, 4096);
            }

            gzclose($handleGzip);
            fclose($handle);

            if (!file_exists($unpackedFilePath)) {
                throw new ArchiverUnpackException('Unpacked file not found: ' . $unpackedFilePath);
            }

            if ($unlink) {
                unlink($packedFilePath);
            }
        } catch (Throwable $exception) {
            if ($throwException) {
                throw $exception;
            }

            return null;
        }

        return realpath($unpackedFilePath);
    }

    public static function packTarGzip(
        array $filePaths,
        string $packedFilePath,
        bool $unlink = false,
        bool $throwException = true
    ): ?string {
        $tarFilePath = static::packTar($filePaths, $packedFilePath . '.tar', $unlink, $throwException);

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $tarGzipFilePath = static::packGzip($tarFilePath, $packedFilePath, true, $throwException);

        return $tarGzipFilePath;
    }

    public static function unpackTarGzip(
        string $packedFilePath,
        string $extractPath = null,
        bool $unlink = false,
        bool $throwException = true
    ): ?array {
        $tarFilePath = static::unpackGzip($packedFilePath, null, $unlink, $throwException);

        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $filePaths = static::unpackTar($tarFilePath, $extractPath, true, $throwException);

        return $filePaths;
    }
}