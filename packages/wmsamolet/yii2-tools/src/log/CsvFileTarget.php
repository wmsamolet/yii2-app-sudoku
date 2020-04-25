<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace wmsamolet\yii2\tools\log;

use Exception;
use Throwable;
use wmsamolet\yii2\tools\helpers\CsvHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\log\FileTarget;
use yii\log\Logger;
use yii\log\LogRuntimeException;
use yii\web\Request;

class CsvFileTarget extends FileTarget
{
    /**
     * Writes log messages to a file.
     * Starting from version 2.0.14, this method throws LogRuntimeException in case the log can not be exported.
     *
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException if unable to open the log file for writing
     * @throws \yii\log\LogRuntimeException if unable to write complete log to file
     */
    public function export(): void
    {
        $logPath = dirname($this->logFile);

        FileHelper::createDirectory($logPath, $this->dirMode);

        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages));

        if (($fp = @fopen($this->logFile, 'ab')) === false) {
            throw new InvalidConfigException("Unable to append to log file: {$this->logFile}");
        }

        @flock($fp, LOCK_EX);

        if ($this->enableRotation) {
            // clear stat cache to ensure getting the real current file size and not a cached one
            // this may result in rotating twice when cached file size is used on subsequent calls
            clearstatcache();
        }

        if ($this->enableRotation && @filesize($this->logFile) > $this->maxFileSize * 1024) {
            @flock($fp, LOCK_UN);
            @fclose($fp);

            $this->rotateFiles();

            $writeResult = @file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);

            if ($writeResult === false) {
                $error = error_get_last();

                throw new LogRuntimeException("Unable to export log through file!: {$error['message']}");
            }

            $textSize = strlen($text);

            if ($writeResult < $textSize) {
                throw new LogRuntimeException("Unable to export whole log through file! Wrote {$writeResult} out of {$textSize} bytes.");
            }
        } else {
            $writeResult = @fwrite($fp, $text);

            if ($writeResult === false) {
                $error = error_get_last();
                throw new LogRuntimeException("Unable to export log through file!: {$error['message']}");
            }

            $textSize = strlen($text);

            if ($writeResult < $textSize) {
                throw new LogRuntimeException("Unable to export whole log through file! Wrote {$writeResult} out of {$textSize} bytes.");
            }

            @flock($fp, LOCK_UN);
            @fclose($fp);
        }

        if ($this->fileMode !== null) {
            @chmod($this->logFile, $this->fileMode);
        }
    }

    public function collect($messages, $final): void
    {
        $this->messages = array_merge(
            $this->messages,
            static::filterMessages(
                $messages,
                $this->getLevels(),
                $this->categories,
                $this->except
            )
        );

        $count = count($this->messages);

        if ($count > 0 && ($final || ($this->exportInterval > 0 && $count >= $this->exportInterval))) {
            // set exportInterval to 0 to avoid triggering export again while exporting
            $oldExportInterval = $this->exportInterval;

            $this->exportInterval = 0;
            $this->export();
            $this->exportInterval = $oldExportInterval;

            $this->messages = [];
        }
    }

    /**
     * ```
     * [
     *   [0] => message (mixed, can be a string or some complex data, such as an exception object)
     *   [1] => level (integer)
     *   [2] => category (string)
     *   [3] => timestamp (float, obtained by microtime(true))
     *   [4] => traces (array, debug backtrace, contains the application code call stacks)
     *   [5] => memory usage in bytes (int, obtained by memory_get_usage()), available since version 2.0.11.
     * ]
     * ```
     *
     * @param array $message
     *
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @return string
     */
    public function formatMessage($message): string
    {
        [$text, $level, $category, $timestamp, $traces, $memory] = $message;

        $level = Logger::getLevelName($level);

        if (!is_string($text)) {
            if ($text instanceof Throwable || $text instanceof Exception) {
                $text = (string)$text;
            } else {
                $text = VarDumper::export($text);
            }
        }

        $request = Yii::$app->getRequest();
        $ip = $request instanceof Request ? $request->getUserIP() : '';

        /* @var $user \yii\web\User */
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;

        if ($user && ($identity = $user->getIdentity(false))) {
            $userId = $identity->getId();
        } else {
            $userId = '';
        }

        /* @var $session \yii\web\Session */
        $session = Yii::$app->has('session', true) ? Yii::$app->get('session') : null;
        $sessionId = $session && $session->getIsActive() ? $session->getId() : '';

        $traces = !is_array($traces) ? '' : array_map(function ($trace) {
            return "in {$trace['file']}:{$trace['line']}";
        }, $traces);

        return CsvHelper::arrayToRowString([
            $this->getTime($timestamp),
            $userId,
            $sessionId,
            $ip,
            $level,
            $category,
            $text,
            $traces,
            $this->getContextMessage(),
            $memory,
            $timestamp,
        ]);
    }

    protected function getContextMessage()
    {
        $context = ArrayHelper::filter($GLOBALS, $this->logVars);

        foreach ($this->maskVars as $var) {
            if (ArrayHelper::getValue($context, $var) !== null) {
                ArrayHelper::setValue($context, $var, '***');
            }
        }

        return json_encode($context);
    }
}