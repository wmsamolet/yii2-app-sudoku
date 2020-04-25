<?php

namespace wmsamolet\yii2\tools\dataProviders;

use wmsamolet\yii2\tools\helpers\ArrayHelper;
use wmsamolet\yii2\tools\models\forms\ArrayModelForm;
use Yii;
use yii\data\ArrayDataProvider as YiiArrayDataProvider;

/**
 * Class ArrayDataProvider
 *
 * @package wmsamolet\yii2\tools\dataProviders
 *
 * @property bool|null|\yii\base\Model $filterModel
 * @property string $filterModelName
 */
class ArrayDataProvider extends YiiArrayDataProvider
{
    public const FILTER_REQUEST_METHOD_GET = 'get';
    public const FILTER_REQUEST_METHOD_POST = 'post';

    /** @var bool|null|\yii\base\Model */
    protected $filterModel = true;

    /** @var string */
    protected $filterModelName;

    /** @var string */
    public $filterRequestMethod = self::FILTER_REQUEST_METHOD_GET;

    public function init(): void
    {
        $this->filterModel = $this->getFilterModel();

        parent::init();
    }

    public function prepare($forcePrepare = false): void
    {
        $this->filter();

        parent::prepare($forcePrepare);
    }

    /**
     * @return bool|\yii\base\Model|null
     */
    public function getFilterModel()
    {
        if ($this->filterModel === true) {
            $this->setFilterModel(true);
        }

        return $this->filterModel;
    }

    /**
     * @param bool|\yii\base\Model|null $filterModel
     *
     */
    public function setFilterModel($filterModel): void
    {
        $this->filterModel = $filterModel;

        if ($this->filterModel === true) {
            $this->filterModel = new ArrayModelForm();
        }
    }

    /**
     * @return string
     */
    public function getFilterModelName(): string
    {
        return $this->filterModelName;
    }

    /**
     * @param string $filterModelName
     */
    public function setFilterModelName(string $filterModelName): void
    {
        $this->filterModelName = $filterModelName;

        if ($this->filterModel instanceof ArrayModelForm) {
            $this->filterModel->formModelName = $this->filterModelName;
        }
    }

    public function getSort()
    {
        $sort = parent::getSort();

        foreach ($sort->attributes as $k => $v) {
            if (is_int($k) && is_string($v)) {
                unset($sort->attributes[$k]);

                $sort->attributes[$v] = [
                    'asc' => [$v => SORT_ASC],
                    'desc' => [$v => SORT_DESC],
                ];
            }
        }

        return $sort;
    }

    /**
     * @param mixed[][] $models
     * @param \yii\data\Sort $sort
     *
     * @return array
     */
    protected function sortModels($models, $sort): array
    {
        $orders = $sort->getOrders();

        if (!empty($orders)) {
            ArrayHelper::multisort(
                $models,
                array_keys($orders),
                array_values($orders),
                SORT_NATURAL
            );
        }

        return $models;
    }

    protected function filter(): void
    {
        if ($this->filterModel instanceof ArrayModelForm) {
            $this->filterModel->formModelName = $this->filterModelName;

            $request = Yii::$app->getRequest();
            $formName = $this->filterModel->formName();
            $filters = $this->filterRequestMethod === self::FILTER_REQUEST_METHOD_GET
                ? $request->get($formName, [])
                : $request->post($formName, []);

            $this->filterModel->formAttributes = $this->filterModel->filters = $filters;

            if (is_array($this->allModels)) {
                $this->allModels = $this->filterModel->filter(
                    $this->allModels
                );

                $this->setTotalCount(count($this->allModels));
            }
        }
    }
}