<?php

namespace wmsamolet\yii2\modules\sudoku\controllers;

use wmsamolet\yii2\modules\sudoku\models\SudokuGame;
use wmsamolet\yii2\modules\sudoku\services\GameService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;

class GameController extends Controller
{
    /**
     * @var \wmsamolet\yii2\modules\sudoku\services\GameService
     */
    private $gameService;

    public function __construct(
        $id,
        $module,
        GameService $gameService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);

        $this->gameService = $gameService;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['play', 'list'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate(): string
    {
        $model = new SudokuGame();
        $model->size = 9;
        $model->difficulty = 0;

        if ($model->load(Yii::$app->request->post())) {
            $model = $this->gameService->create(
                $model->size,
                $model->difficulty,
                Yii::$app->user->getId()
            );

            $this->redirect(['play', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionPlay(int $id): string
    {
        /** @var \wmsamolet\yii2\modules\sudoku\SudokuModule $module */
        $module = $this->module;

        $game = $this->gameService->getById($id);

        $accessToken = $this->gameService->generatePlayerAccessToken(
            Yii::$app->getUser()->getId(),
            $module->accessTokenSalt
        );

        return $this->render('play', [
            'game' => $game,
            'accessToken' => $accessToken,
        ]);
    }

    public function actionList(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->gameService->getAllNotFinishedQuery(),
        ]);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLadder(): string
    {
        $dataProvider = $this->gameService->getLadderData();

        return $this->render('ladder', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
