<?php
/** @noinspection OffsetOperationsInspection */

namespace wmsamolet\yii2\tools\dataProviders;

use wmsamolet\yii2\tools\helpers\ArrayHelper;
use Yii;
use yii\base\Exception;
use yii\data\BaseDataProvider;

class CsvDataProvider extends BaseDataProvider
{
    /** @var string name of the CSV file to read */
    public $filePath;

    /** @var string|callable name of the key column or a callable returning it */
    public $key;

    /** @var array */
    public $firstRowAsModelKeys = false;

    /** @var int */
    public $length = 0;

    /** @var string */
    public $delimiter = ',';

    /** @var string */
    public $enclosure = '"';

    /** @var string */
    public $escape = '\\';

    /** @var resource */
    protected $fileResource; // SplFileObject is very convenient for seeking to particular line in a file

    /** @var array */
    protected $modelKeys = [];

    public function __destruct()
    {
        fclose($this->fileResource);
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $filePath = Yii::getAlias($this->filePath);

        if (!file_exists($filePath)) {
            throw new Exception("File {$filePath} not found");
        }

        $this->fileResource = fopen($filePath, 'rb');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareModels(): array
    {
        $models = [];
        $pagination = $this->getPagination();

        rewind($this->fileResource);

        if ($this->firstRowAsModelKeys) {
            $this->modelKeys = $this->getCsvRowData();
        }

        if ($pagination !== false) {
            $pagination->totalCount = $this->getTotalCount();
            $offset = $pagination->getOffset();
            $limit = $pagination->getLimit();

            /** @noinspection PhpAssignmentInConditionInspection */
            for ($csvRowNumber = 0; $csvRowData = $this->getCsvRowData(); $csvRowNumber++) {
                if ($csvRowNumber < $offset) {
                    continue;
                }

                if ($csvRowNumber > $offset + ($limit - 1)) {
                    break;
                }

                if (count($this->modelKeys)) {
                    $model = [];

                    foreach ($csvRowData as $csvColumnIndex => $csvColumnData) {
                        $modelKey = $this->modelKeys[$csvColumnIndex] ?? $csvColumnIndex;
                        $model[$modelKey] = $csvColumnData;
                    }
                } else {
                    $model = $csvRowData;
                }

                $key = ArrayHelper::getValue($model, $this->key, count($models));

                $models[$key] = $model;
            }
        } else {
            /** @noinspection PhpAssignmentInConditionInspection */
            while ($csvRowData = $this->getCsvRowData()) {
                $key = ArrayHelper::getValue($csvRowData, $this->key, count($models));
                $models[$key] = $csvRowData;
            }
        }

        return $models;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models): array
    {
        if ($this->key !== null) {
            $keys = [];

            foreach ($models as $model) {
                if (is_scalar($this->key)) {
                    $keys[] = $model[$this->key];
                } elseif (is_callable($this->key)) {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        }

        return array_keys($models);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount(): int
    {
        $count = 0;

        rewind($this->fileResource);

        while ($this->getCsvRowData() !== false) {
            $count++;
        }

        rewind($this->fileResource);

        if ($this->firstRowAsModelKeys) {
            $count--;
        }

        return $count >= 0 ? $count : 0;
    }

    /**
     * @return null|array|false
     */
    protected function getCsvRowData()
    {
        return fgetcsv(
            $this->fileResource,
            $this->length,
            $this->delimiter,
            $this->enclosure,
            $this->escape
        );
    }
}