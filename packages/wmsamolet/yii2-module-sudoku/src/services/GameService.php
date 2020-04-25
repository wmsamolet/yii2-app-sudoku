<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace wmsamolet\yii2\modules\sudoku\services;

use AbcAeffchen\sudoku\Sudoku;
use wmsamolet\yii2\modules\sudoku\exceptions\GameServiceException;
use wmsamolet\yii2\modules\sudoku\models\SudokuGame;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;

class GameService
{
    /**
     * @var \wmsamolet\yii2\modules\sudoku\services\GameMatrixService
     */
    private $gameMatrixService;

    public function __construct(GameMatrixService $gameMatrixService)
    {
        $this->gameMatrixService = $gameMatrixService;
    }

    public function findById(int $id): ?SudokuGame
    {
        return SudokuGame::findOne(['id' => $id]);
    }

    public function getById(int $id): SudokuGame
    {
        $game = $this->findById($id);

        if (!$game) {
            throw new GameServiceException("Game with id {$id} not found", 500);
        }

        return $game;
    }

    public function getAllNotFinishedQuery(): ActiveQuery
    {
        return SudokuGame::find()->where(['finished_at' => null]);
    }

    public function getLadderData(): ActiveDataProvider
    {
        $q = SudokuGame::find()
            ->select([
                'winner_player_id',
                new Expression('COUNT(winner_player_id) as count_wins'),
            ])
            ->where(['>', 'winner_player_id', 0])
            ->groupBy(['winner_player_id'])
            ->orderBy(['count_wins' => SORT_DESC])
            ->asArray();

        return new ActiveDataProvider(['query' => $q, 'key' => 'winner_player_id']);
    }

    public function create(int $matrixSize = 9, int $matrixDifficulty = 0, int $ownerPlayerId = 0): SudokuGame
    {
        [$matrix, $solution] = Sudoku::generateWithSolution($matrixSize, $matrixDifficulty);

        $matrix = $this->gameMatrixService->convertFromArray($matrix);
        $solution = $this->gameMatrixService->convertSolutionFromArray($solution);

        $newGame = new SudokuGame();
        $newGame->size = $matrixSize;
        $newGame->difficulty = $matrixDifficulty;
        $newGame->matrix = json_encode($matrix, JSON_PRETTY_PRINT);
        $newGame->solution = json_encode($solution);
        $newGame->owner_player_id = $ownerPlayerId;

        if (!$newGame->insert()) {
            throw new GameServiceException('Create game save error', 500);
        }

        return $newGame;
    }

    public function checkMove(int $gameId, int $cellId, int $cellValue): bool
    {
        $game = $this->getById($gameId);

        $solution = json_decode($game->solution, true);

        return isset($solution[$cellId]) && $solution[$cellId] === $cellValue;
    }

    public function update(int $id, array $attributes): int
    {
        return $this->getById($id)->updateAttributes($attributes);
    }

    public function generatePlayerAccessToken(int $userId, string $salt = null): string
    {
        return md5($userId . $salt);
    }
}