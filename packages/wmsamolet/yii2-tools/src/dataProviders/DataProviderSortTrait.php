<?php

namespace wmsamolet\yii2\tools\dataProviders;

use Yii;
use yii\data\BaseDataProvider;
use yii\helpers\ArrayHelper;

trait DataProviderSortTrait
{
    public function addSortAttribute(
        BaseDataProvider $dataProvider,
        string $attribute,
        string $sortAsc = null,
        string $sortDesc = null,
        int $defaultSort = SORT_ASC,
        string $label = null
    ): void {
        $sort = $dataProvider->getSort();
        $sort->attributes = ArrayHelper::merge(
            $dataProvider->getSort()->attributes,
            [
                $attribute => [
                    'asc' => [$sortAsc ?? $attribute => SORT_ASC],
                    'desc' => [$sortDesc ?? $attribute => SORT_DESC],
                    'default' => $defaultSort,
                    'label' => $label,
                ],
            ]
        );
    }

    public function rotateAttributeOrders(BaseDataProvider $dataProvider): void
    {
        $sort = $dataProvider->getSort();

        $sessionKey = 'orders_' . static::class;
        $sessionOrders = Yii::$app->session->get($sessionKey);

        $attributeOrders = $sort->getAttributeOrders();

        foreach ($attributeOrders as $attribute => $order) {
            $sessionAttributeOrder = ArrayHelper::getValue($sessionOrders, $attribute);

            if (is_array($sessionAttributeOrder) && array_sum($sessionAttributeOrder) >= SORT_ASC + SORT_DESC) {
                unset($attributeOrders[$attribute], $sessionOrders[$attribute]);
            } else {
                ArrayHelper::setValue($sessionOrders, [$attribute, $order], $order);
            }
        }

        Yii::$app->session->set($sessionKey, $sessionOrders);

        $sort->setAttributeOrders($attributeOrders);
    }
}