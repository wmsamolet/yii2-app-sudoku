<?php

namespace wmsamolet\yii2\modules\sudoku\services;

use Wmsamolet\PhpJsonRpc2\JsonRpc2ErrorResponse;
use Wmsamolet\PhpJsonRpc2\JsonRpc2Request;
use Wmsamolet\PhpJsonRpc2\JsonRpc2Response;
use Wmsamolet\PhpWebsocket\ServerClientInterface;
use wmsamolet\yii2\modules\sudoku\SudokuModule;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\User;

class ServerService
{
    /** @var \wmsamolet\yii2\modules\sudoku\services\GameService */
    private $gameService;

    /** @var \wmsamolet\yii2\modules\sudoku\services\GameMovementService */
    private $gameMoveService;

    /** @var \wmsamolet\yii2\modules\sudoku\services\GameMatrixService */
    private $gameMatrixService;

    public function __construct(
        GameService $gameService,
        GameMovementService $gameMoveService,
        GameMatrixService $gameMatrixService
    ) {
        $this->gameService = $gameService;
        $this->gameMoveService = $gameMoveService;
        $this->gameMatrixService = $gameMatrixService;
    }

    public function ping(ServerClientInterface $serverClient): void
    {
        $this->sendResponse($serverClient, 'pong');
    }

    public function authorize(
        ServerClientInterface $serverClient,
        int $playerId,
        string $accessToken,
        string $salt
    ): bool {
        if ($accessToken !== $this->gameService->generatePlayerAccessToken($playerId, $salt)) {
            $this->sendResponseError($serverClient, 500, 'Invalid access token');

            return false;
        }

        $this->setClientUserData($serverClient, ['id' => $playerId]);

        $this->sendResponse($serverClient, [
            'message' => Yii::t(
                SudokuModule::T_CATEGORY,
                'Player with id#{playerId} authorized successfully!',
                [
                    'playerId' => $playerId,
                ]
            ),
        ]);

        return true;
    }

    public function play(
        ServerClientInterface $serverClient,
        int $gameId
    ): void {
        $matrix = $this->getFilledGameMatrix($gameId);
        $playerId = $this->getClientUserId($serverClient);
        $message = Yii::t(
            SudokuModule::T_CATEGORY,
            'Player id#{playerId} connected to the game',
            [
                'playerId' => $playerId,
            ]
        );

        $this->updatePlayersGameData($serverClient, $gameId, $matrix, $message);
        $this->checkWinner($serverClient, $gameId, $matrix);
    }

    public function makeAMove(
        ServerClientInterface $serverClient,
        int $gameId,
        int $cellId,
        int $cellValue
    ): void {
        $matrix = $this->getFilledGameMatrix($gameId);
        $playerId = $this->getClientUserId($serverClient);
        $cellStatus = (int)$this->gameService->checkMove($gameId, $cellId, $cellValue);

        if ($this->checkWinner($serverClient, $gameId, $matrix)) {
            return;
        }

        $this->gameMoveService->make($gameId, $cellId, $cellValue, $cellStatus, $playerId);

        if ($cellStatus === $this->gameMoveService::CELL_STATUS_OK) {
            $matrix = $this->gameMatrixService->setCellValue($matrix, $cellId, $cellValue, $playerId);
        }

        $message = Yii::t(
            SudokuModule::T_CATEGORY,
            'Player id#{playerId} made a {right} movement in the field {cellId} with value {cellValue}',
            [
                'playerId' => $playerId,
                'cellId' => $cellId,
                'cellValue' => $cellValue,
                'right' => $cellStatus
                    ? Yii::t(SudokuModule::T_CATEGORY, 'right')
                    : Yii::t(SudokuModule::T_CATEGORY, 'wrong'),
            ]
        );

        $this->updatePlayersGameData(
            $serverClient,
            $gameId,
            $matrix,
            $message,
            $cellStatus === $this->gameMoveService::CELL_STATUS_OK ? 'success' : 'warning'
        );
    }

    public function onOpenConnection(ServerClientInterface $serverClient): void
    {
        $playerId = $this->getClientUserId($serverClient);

        /** @var ServerClientInterface $otherServerClient */
        foreach ($serverClient->getServer()->getClients() as $otherServerClient) {
            if ($otherServerClient === $serverClient) {
                continue;
            }

            $this->sendResponse($otherServerClient, [
                'message' => Yii::t(
                    SudokuModule::T_CATEGORY,
                    'Player id#{playerId} connected',
                    [
                        'playerId' => $playerId,
                    ]
                ),
            ]);
        }
    }

    public function onCloseConnection(ServerClientInterface $serverClient): void
    {
        $playerId = $this->getClientUserId($serverClient);

        /** @var ServerClientInterface $otherServerClient */
        foreach ($serverClient->getServer()->getClients() as $otherServerClient) {
            if ($otherServerClient === $serverClient) {
                continue;
            }

            $this->sendResponse($otherServerClient, [
                'message' => Yii::t(
                    SudokuModule::T_CATEGORY,
                    'Player id#{playerId} disconnected',
                    [
                        'playerId' => $playerId,
                    ]
                ),
            ]);
        }
    }

    public function processRequest(ServerClientInterface $serverClient, string $accessTokenSalt = null): void
    {
        $message = $serverClient->getMessage();
        $messageData = Json::decode($message);

        $id = ArrayHelper::getValue($messageData, 'id');
        $method = ArrayHelper::getValue($messageData, 'method');
        $params = (array)ArrayHelper::getValue($messageData, 'params', []);

        if ($method === 'authorize') {
            $params[2] = $accessTokenSalt;
        } elseif (in_array($method, ['processRequest', 'sendResponse', 'sendResponseError'])) {
            $this->sendResponseError($serverClient, 405, 'Method not allowed');

            return;
        }

        $serverClient->getStorage()->offsetSet(
            JsonRpc2Request::class,
            new JsonRpc2Request($method, $params, $id)
        );

        if (!in_array($method, ['ping', 'authorize']) && !$this->getClientUserId($serverClient)) {
            $this->sendResponseError($serverClient, 401, 'Unauthorized');

            return;
        }

        array_unshift($params, $serverClient);
        call_user_func_array([$this, $method], $params);
    }

    public function sendResponse(ServerClientInterface $serverClient, $data, string $requestId = null): bool
    {
        if ($requestId === null && $serverClient->getStorage()->offsetExists(JsonRpc2Request::class)) {
            $requestId = $serverClient->getStorage()->offsetGet(JsonRpc2Request::class)->getId();

            $serverClient->getStorage()->offsetUnset(JsonRpc2Request::class);
        }

        $response = (new JsonRpc2Response($data, $requestId))
            ->addData(['date' => date('Y-m-d H:i:s')]);

        echo "Send response to #{$serverClient->getId()}: {$response}\n\n";

        return $serverClient->sendMessage($response);
    }

    public function sendResponseError(
        ServerClientInterface $serverClient,
        int $errorCode,
        string $errorMessage,
        array $errorData = [],
        string $requestId = null
    ): bool {
        if ($requestId === null && $serverClient->getStorage()->offsetExists(JsonRpc2Request::class)) {
            $requestId = $serverClient->getStorage()->offsetGet(JsonRpc2Request::class)->getId();

            $serverClient->getStorage()->offsetUnset(JsonRpc2Request::class);
        }

        return $serverClient->sendMessage(
            (new JsonRpc2ErrorResponse($errorCode, $errorMessage, $requestId))
                ->addData(['date' => date('Y-m-d H:i:s')])
                ->addData($errorData)
        );
    }

    protected function checkWinner(
        ServerClientInterface $serverClient,
        int $gameId,
        array $matrix
    ): bool {
        if ($this->gameMatrixService->count($matrix, false) > 0) {
            return false;
        }

        $gameMovement = $this->gameMoveService->getLastOkMovement($gameId);
        $winnerPlayerId = $gameMovement->player_id;

        $this->gameService->update($gameId, ['winner_player_id' => $winnerPlayerId]);

        $message = Yii::t(
            SudokuModule::T_CATEGORY,
            'Game finished! Winner: player id#{winnerPlayerId}',
            [
                'winnerPlayerId' => $winnerPlayerId,
            ]
        );

        /** @var ServerClientInterface $otherServerClient */
        foreach ($serverClient->getServer()->getClients() as $otherServerClient) {
            $this->sendResponse($otherServerClient, [
                'message' => $message,
                'messageType' => 'success',
                'winnerPlayerId' => $winnerPlayerId,
            ], 'showWinner');
        }

        return true;
    }

    protected function updatePlayersGameData(
        ServerClientInterface $serverClient,
        int $gameId,
        array $matrix = null,
        string $message = null,
        string $messageType = 'info'
    ): void {
        $matrix = $matrix ?? $this->getFilledGameMatrix($gameId);
        $cellsFilled = $this->gameMatrixService->count($matrix);
        $cellsEmpty = $this->gameMatrixService->count($matrix, false);

        /** @var ServerClientInterface $otherServerClient */
        foreach ($serverClient->getServer()->getClients() as $otherServerClient) {
            $this->sendResponse(
                $otherServerClient,
                [
                    'message' => $message,
                    'messageType' => $messageType,
                    'matrix' => $matrix,
                    'cellsFilled' => $cellsFilled,
                    'cellsEmpty' => $cellsEmpty,
                ],
                'updateGameData'
            );
        }
    }

    protected function setClientUserData(ServerClientInterface $serverClient, array $data = []): self
    {
        $userData = $this->getClientUserData($serverClient);
        $userData = array_merge_recursive($userData ?? [], $data);

        $serverClient->getStorage()->offsetSet(User::class, $userData);

        return $this;
    }

    protected function getClientUserData(ServerClientInterface $serverClient): ?array
    {
        return $serverClient->getStorage()->offsetExists(User::class)
            ? $serverClient->getStorage()->offsetGet(User::class)
            : null;
    }

    protected function getClientUserId(ServerClientInterface $serverClient): int
    {
        return (int)ArrayHelper::getValue((array)$this->getClientUserData($serverClient), 'id');
    }

    protected function getFilledGameMatrix(int $gameId): array
    {
        $game = $this->gameService->getById($gameId);
        $gameMatrix = json_decode($game->matrix, true);
        $gameMovements = $this->gameMoveService->getGameMovements($gameId);
        $cellsValues = [];

        foreach ($gameMovements as $gameMovement) {
            $cellsValues[$gameMovement->cell_id] = [
                'pid' => $gameMovement->player_id,
                'val' => $gameMovement->cell_value,
            ];
        }

        return $this->gameMatrixService->fillCells($gameMatrix, $cellsValues);
    }
}