<?php
/** @noinspection MultipleReturnStatementsInspection */

namespace wmsamolet\yii2\tools\dataProviders;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\Connection;
use yii\db\QueryInterface;
use yii\di\Instance;

class ActiveArrayDataProvider extends ArrayDataProvider
{
    /**
     * @var QueryInterface|ActiveQuery the query that is used to fetch data models and [[totalCount]]
     * if it is not explicitly set.
     */
    public $query;

    /**
     * @var string|callable the column that is used as the key of the data models.
     * This can be either a column name, or a callable that returns the key value of a given data model.
     *
     * If this is not set, the following rules will be used to determine the keys of the data models:
     *
     * - If [[query]] is an [[\yii\db\ActiveQuery]] instance, the primary keys of [[\yii\db\ActiveQuery::modelClass]] will be used.
     * - Otherwise, the keys of the [[models]] array will be used.
     *
     * @see getKeys()
     */
    public $key;

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * If not set, the default DB connection will be used.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db;

    /**
     * @var string
     */
    public $modelClass;

    /**
     * @var array
     */
    public $allModels;

    /**
     * @var null|callable
     */
    public $prepareModelCallback;

    /**
     * Initializes the DB connection component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     *
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public function init(): void
    {
        parent::init();

        if (is_string($this->db)) {
            $this->db = Instance::ensure($this->db, Connection::class);
        }
    }

    protected function prepareModels()
    {
        if (!is_array($this->allModels)) {
            if (!$this->query instanceof QueryInterface) {
                throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
            }

            $query = clone $this->query;

            if ($query instanceof ActiveQuery) {
                $this->modelClass = $this->modelClass ?? $query->modelClass;

                $query->asArray();
            }

            $profileToken = static::class . '::prepareModels()';
            Yii::beginProfile($profileToken);

            foreach ($query->each(1000) as $k => $model) {
                $this->allModels[$k] = $model;

                if ($this->prepareModelCallback && is_callable($this->prepareModelCallback)) {
                    call_user_func($this->prepareModelCallback, $model, $k, $this);
                }
            }

            Yii::endProfile($profileToken);
        }

        $models = [];

        if (($sort = $this->getSort()) !== false) {
            $this->allModels = $this->sortModels($this->allModels, $sort);
        }

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();

            if ($pagination->getPageSize() > 0 && is_array($this->allModels)) {
                $models = array_slice($this->allModels, $pagination->getOffset(), $pagination->getLimit(), true);
            }
        }

        if ($this->modelClass) {
            foreach ($models as $k => $model) {
                $className = is_string($this->modelClass) ? $this->modelClass : get_class($this->modelClass);
                $models[$k] = new $className($model);
            }
        }

        return $models;
    }

    /**
     * @param array $models
     *
     * @return array
     */
    protected function prepareKeys($models): array
    {
        $keys = [];

        if ($this->key !== null) {
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        }

        if ($this->query instanceof ActiveQueryInterface) {
            /* @var $class \yii\db\ActiveRecordInterface */
            $class = $this->query->modelClass;
            $pks = $class::primaryKey();

            if (count($pks) === 1) {
                $pk = $pks[0];
                foreach ($models as $model) {
                    $keys[] = $model[$pk];
                }
            } else {
                foreach ($models as $model) {
                    $kk = [];
                    foreach ($pks as $pk) {
                        $kk[$pk] = $model[$pk];
                    }
                    $keys[] = $kk;
                }
            }

            return $keys;
        }

        return array_keys($models);
    }

    public function setSort($value): void
    {
        parent::setSort($value);

        $sort = $this->getSort();

        if ($sort !== false && $this->query instanceof ActiveQueryInterface) {
            /* @var $modelClass Model */
            $modelClass = $this->query->modelClass;
            $model = $modelClass::instance();

            if (empty($sort->attributes)) {
                foreach ($model->attributes() as $attribute) {
                    $sort->attributes[$attribute] = [
                        'asc' => [$attribute => SORT_ASC],
                        'desc' => [$attribute => SORT_DESC],
                        'label' => $model->getAttributeLabel($attribute),
                    ];
                }
            } else {
                foreach ($sort->attributes as $attribute => $config) {
                    if (!isset($config['label'])) {
                        $sort->attributes[$attribute]['label'] = $model->getAttributeLabel($attribute);
                    }
                }
            }
        }
    }

    public function __clone()
    {
        if (is_object($this->query)) {
            $this->query = clone $this->query;
        }

        parent::__clone();
    }
}