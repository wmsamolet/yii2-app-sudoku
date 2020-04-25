<?php

namespace wmsamolet\yii2\modules\sudoku\services;

use wmsamolet\yii2\modules\sudoku\exceptions\GameMoveServiceException;
use wmsamolet\yii2\modules\sudoku\models\SudokuGameMovement;

class GameMovementService
{
    public const CELL_STATUS_OK = 1;
    public const CELL_STATUS_ERROR = 0;

    public function find(int $gameId, int $cellId, int $cellStatus = null, int $playerId = null): ?SudokuGameMovement
    {
        $q = SudokuGameMovement::find()->where(['game_id' => $gameId, 'cell_id' => $cellId]);

        if ($cellStatus !== null) {
            $q->andWhere(['cell_status' => $cellStatus]);
        }

        if ($playerId !== null) {
            $q->andWhere(['player_id' => $playerId]);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $q->one();
    }

    public function get(int $gameId, int $cellId, int $cellStatus = null, int $playerId = null): ?SudokuGameMovement
    {
        $gameMovement = $this->find($gameId, $cellId, $cellStatus, $playerId);

        if (!$gameMovement) {
            throw new GameMoveServiceException(
                "Game movement with [gameId:{$gameId}, cellId:{$cellId}, cellStatus:{$cellStatus}, playerId:{$playerId}] not found",
                500
            );
        }

        return $gameMovement;
    }

    public function findById(int $gameId): ?SudokuGameMovement
    {
        return SudokuGameMovement::findOne(['id' => $gameId]);
    }

    public function getById(int $id): SudokuGameMovement
    {
        $gameMovement = $this->findById($id);

        if (!$gameMovement) {
            throw new GameMoveServiceException("Game movement with id {$id} not found", 500);
        }

        return $gameMovement;
    }

    /**
     * @return SudokuGameMovement[]
     */
    public function getGameMovements(int $gameId, int $cellStatus = self::CELL_STATUS_OK): array
    {
        return SudokuGameMovement::findAll(['game_id' => $gameId, 'cell_status' => $cellStatus]);
    }

    public function findLastOkMovement(int $gameId): ?SudokuGameMovement
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return SudokuGameMovement::find()
            ->where(['game_id' => $gameId])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    public function getLastOkMovement(int $gameId): SudokuGameMovement
    {
        $gameMovement = $this->findLastOkMovement($gameId);

        if (!$gameMovement) {
            throw new GameMoveServiceException('Last ok game movement not found', 500);
        }

        return $gameMovement;
    }

    public function make(int $gameId, int $cellId, int $cellValue, int $cellStatus, int $playerId): SudokuGameMovement
    {
        $newGameMovement = new SudokuGameMovement();
        $newGameMovement->game_id = $gameId;
        $newGameMovement->cell_id = $cellId;
        $newGameMovement->cell_value = $cellValue;
        $newGameMovement->cell_status = $cellStatus;
        $newGameMovement->player_id = $playerId;

        if (!$newGameMovement->save()) {
            throw new GameMoveServiceException('Create game movement save error', 500);
        }

        return $newGameMovement;
    }

    public function update(int $gameId, array $attributes): int
    {
        $attributes['updated_at'] = $attributes['updated_at'] ?? time();

        return $this->getById($gameId)->updateAttributes($attributes);
    }
}