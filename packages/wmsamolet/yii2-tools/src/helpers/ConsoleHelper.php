<?php
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

namespace wmsamolet\yii2\tools\helpers;

use Symfony\Component\Process\Process;
use yii\helpers\Console;

/**
 * Class ConsoleHelper
 */
class ConsoleHelper extends Console
{
    /**
     * @param string $command
     * @param null $input
     * @return Process
     */
    public static function exec($command, $input = null): Process
    {
        $process = new Process($command);
        $process->setTimeout(false);

        if ($input !== null) {
            $process->setPty(true);
            $process->setInput($input);
        }

        $process->start();

        return $process;
    }

    /**
     * Gives the user an option to choose from. Giving '?' as an input will show
     * a list of options to choose from and their explanations.
     *
     * @param string $prompt the prompt message
     * @param array $options Key-value array of options to choose from. Key is what is inputed and used, value is
     * what's displayed to end user by help command.
     *
     * @return string An option character the user chose
     */
    public static function choice($prompt, array $options = []): ?string
    {
        static::stdout("\n");
        static::stdout("$prompt\n");
        static::stdout("\n");

        foreach ($options as $key => $value) {
            static::output("  [{$key}] {$value} ");
        }

        static::stdout("\n");

        top:

        static::stdout('  You choice: ');

        $input = static::stdin();

        if (array_key_exists($input, $options)) {
            return $options[$input];
        }

        goto top;
    }
}