<?php

namespace wmsamolet\yii2\modules\sudoku\services;

use AbcAeffchen\sudoku\Sudoku;
use wmsamolet\yii2\modules\sudoku\exceptions\GameMatrixServiceException;
use yii\base\InvalidArgumentException;

class GameMatrixService
{
    public const SIZE_4 = 4;
    public const SIZE_9 = 9;
    public const SIZE_16 = 16;

    public const DIFFICULTY_EASY = 10;
    public const DIFFICULTY_NORMAL = 15;
    public const DIFFICULTY_HARD = 20;

    public function solve(array $matrix): bool
    {
        return is_array(
            Sudoku::solve(
                $this->convertToArray($matrix)
            )
        );
    }

    public function validate(array $matrix): bool
    {
        $currentCellId = 0;

        foreach ($matrix as $rowIndex => $row) {
            foreach ($row as $cellIndex => $cell) {
                $currentCellId++;

                if (
                    !array_key_exists('id', $cell)
                    ||
                    !array_key_exists('pid', $cell)
                    ||
                    !array_key_exists('val', $cell)
                    ||
                    $currentCellId !== $cell['id']
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    public function fillCells(array $matrix, array $cellsValues, bool $safe = true): array
    {
        if (!$this->validate($matrix)) {
            throw new GameMatrixServiceException('Invalid matrix');
        }

        $currentCellId = 0;

        foreach ($matrix as $rowIndex => $row) {
            foreach ($row as $cellIndex => $cell) {
                $currentCellId++;

                if (isset($cellsValues[$currentCellId])) {
                    if ($safe && $cell['val'] > 0) {
                        throw new InvalidArgumentException(
                            "Matrix cell [{$rowIndex}, {$cellIndex}, {$currentCellId}] already filled value {$cell['val']}"
                        );
                    }

                    $cell['pid'] = $cellsValues[$currentCellId]['pid'];
                    $cell['val'] = $cellsValues[$currentCellId]['val'];

                    $matrix[$rowIndex][$cellIndex] = $cell;
                }
            }
        }

        return $matrix;
    }

    public function setCellValue(array $matrix, int $cellId, int $value, int $playerId): array
    {
        if (!$this->validate($matrix)) {
            throw new GameMatrixServiceException('Invalid matrix');
        }

        return $this->fillCells($matrix, [
            $cellId => [
                'id' => $cellId,
                'pid' => $playerId,
                'val' => $value,
            ],
        ]);
    }

    public function getCellValue(array $matrix, int $cellId, int $playerId = null): int
    {
        if (!$this->validate($matrix)) {
            throw new GameMatrixServiceException('Invalid matrix');
        }

        $currentCellId = 0;

        foreach ($matrix as $rowIndex => $row) {
            foreach ($row as $cellIndex => $cell) {
                $currentCellId++;

                if ($currentCellId === $cellId) {
                    if ($playerId !== null && $cell['pid'] !== $playerId) {
                        throw new InvalidArgumentException('Matrix cell owned by another player');
                    }

                    return $cell['val'];
                }
            }
        }

        throw new InvalidArgumentException('Matrix cell not found');
    }

    public function count(array $matrix, bool $filled = true): int
    {
        if (!$this->validate($matrix)) {
            throw new GameMatrixServiceException('Invalid matrix');
        }

        $count = 0;

        foreach ($matrix as $row) {
            foreach ($row as $cell) {
                $cellValue = $cell['val'];

                if (($filled && $cellValue) || (!$filled && !$cellValue)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function convertFromArray(array $arrayMatrix): array
    {
        $convertedMatrix = [];
        $cellId = 0;

        foreach ($arrayMatrix as $rowIndex => $row) {
            foreach ($row as $cellIndex => $value) {
                $cellId++;

                if (!isset($convertedMatrix[$rowIndex])) {
                    $convertedMatrix[$rowIndex] = [];
                }

                $convertedMatrix[$rowIndex][$cellIndex] = [
                    'id' => $cellId,
                    'pid' => $value === null ? null : 0,
                    'val' => $value,
                ];
            }
        }

        return $convertedMatrix;
    }

    public function convertToArray(array $matrix): array
    {
        if (!$this->validate($matrix)) {
            throw new GameMatrixServiceException('Invalid matrix');
        }

        $convertedMatrix = [];

        foreach ($matrix as $rowIndex => $row) {
            foreach ($row as $cellIndex => $cell) {
                $convertedMatrix[$rowIndex][$cellIndex] = $cell['val'];
            }
        }

        return $convertedMatrix;
    }

    public function convertSolutionFromArray(array $arrayMatrix): array
    {
        $convertedSolution = [];
        $cellId = 0;

        foreach ($arrayMatrix as $rowIndex => $row) {
            foreach ($row as $cellIndex => $value) {
                $cellId++;
                $convertedSolution[$cellId] = $value;
            }
        }

        return $convertedSolution;
    }

    public static function getSizeList(): array
    {
        return [
            static::SIZE_4 => '4x4',
            static::SIZE_9 => '9x9',
            static::SIZE_16 => '16x16',
        ];
    }

    public static function getDifficultyList(): array
    {
        return [
            static::DIFFICULTY_EASY => 'easy',
            static::DIFFICULTY_NORMAL => 'normal',
            static::DIFFICULTY_HARD => 'hard',
        ];
    }
}