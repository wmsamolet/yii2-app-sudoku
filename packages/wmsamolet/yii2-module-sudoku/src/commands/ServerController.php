<?php
/** @noinspection PhpInternalEntityUsedInspection */

namespace wmsamolet\yii2\modules\sudoku\commands;

use Throwable;
use Wmsamolet\PhpWebsocket\ServerClientInterface;
use Wmsamolet\PhpWebsocket\ServerInterface;
use wmsamolet\yii2\modules\sudoku\services\ServerService;
use Yii;
use yii\console\Controller as ConsoleController;
use yii\console\ExitCode;

class ServerController extends ConsoleController
{
    /** @var \Wmsamolet\PhpWebsocket\ServerInterface */
    private $server;

    /** @var \wmsamolet\yii2\modules\sudoku\services\ServerService */
    private $serverService;

    public function __construct(
        $id,
        $module,
        ServerInterface $server,
        ServerService $serverService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);

        $this->server = $server;
        $this->serverService = $serverService;
    }

    public function actionListen(): int
    {
        $this->server->on($this->server::ON_OPEN, function (ServerClientInterface $serverClient) {
            $this->serverService->onOpenConnection($serverClient);

            echo "Client #{$serverClient->getId()} connected (total: " . $this->server->getClients()->count() . ")\n\n";
        });

        $this->server->on($this->server::ON_CLOSE, function (ServerClientInterface $serverClient) {
            $this->serverService->onCloseConnection($serverClient);

            echo "Client #{$serverClient->getId()} disconnected\n\n";
        });

        $this->server->on($this->server::ON_MESSAGE, function (ServerClientInterface $serverClient) {
            try {
                /** @var \wmsamolet\yii2\modules\sudoku\SudokuModule $module */
                $module = $this->module;

                $this->serverService->processRequest($serverClient, $module->accessTokenSalt);
            } catch (Throwable $exception) {
                $traceLevel = ($log = Yii::$app->get('log')) ? $log->traceLevel : 5;

                /** @var array $traces */
                $traces = array_filter($exception->getTrace(), function ($trace) {
                    return isset($trace['file']);
                });

                $traces = array_slice($traces, 0, $traceLevel);

                $traces = array_map(function ($trace) {
                    $file = $trace['file'] ?? null;
                    $line = $trace['line'] ?? null;

                    return "{$file}:{$line}";
                }, $traces);

                $this->serverService->sendResponseError(
                    $serverClient,
                    $exception->getCode(),
                    $exception->getMessage(),
                    ['error' => ['trace' => $traces]]
                );
            }
        });

        $this->server->start();

        return ExitCode::OK;
    }
}