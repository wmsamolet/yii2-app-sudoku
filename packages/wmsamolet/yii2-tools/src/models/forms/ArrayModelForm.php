<?php

namespace wmsamolet\yii2\tools\models\forms;

use Throwable;
use yii\base\Model;

class ArrayModelForm extends Model
{
    public $filters = [];
    public $formAttributes = [];
    public $formModelName;

    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (Throwable $exception) {
            return $this->formAttributes[$name];
        }
    }

    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (Throwable $exception) {
            $this->formAttributes[$name] = $value;
        }
    }

    public function formName(): string
    {
        return $this->formModelName ?: parent::formName();
    }

    public function filter(array $data): array
    {
        foreach ($this->filters as $searchAttribute => $searchValuesString) {
            if (empty($searchValuesString)) {
                continue;
            }

            if (strpos($searchValuesString, '&&') !== false) {
                $searchValues = array_map('trim', explode('&&', $searchValuesString));
                $filterType = 'AND';
            } elseif (strpos($searchValuesString, '||') !== false) {
                $searchValues = array_map('trim', explode('||', $searchValuesString));
                $filterType = 'OR';
            } else {
                $searchValues = [$searchValuesString];
                $filterType = null;
            }

            foreach ($data as $i => $attributes) {
                if (!array_key_exists($searchAttribute, $attributes)) {
                    continue;
                }

                $countFound = 0;

                foreach ($searchValues as $searchValue) {
                    if (stripos($attributes[$searchAttribute], $searchValue) !== false) {
                        $countFound++;
                    }
                }

                switch ($filterType) {
                    case 'AND':
                        if ($countFound !== count($searchValues)) {
                            unset($data[$i]);
                        }
                        break;
                    case 'OR':
                        if ($countFound === 0) {
                            unset($data[$i]);
                        }
                        break;
                    default:
                        if ($countFound !== 1) {
                            unset($data[$i]);
                        }
                }
            }
        }

        return $data;
    }

    public function isAttributeActive($attribute): bool
    {
        return true;
    }
}