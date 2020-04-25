<?php
/** @noinspection NotOptimalIfConditionsInspection */
/** @noinspection MultipleReturnStatementsInspection */

namespace wmsamolet\yii2\tools\helpers;

use Throwable;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url as YiiUrl;
use yii\web\Application;
use yii\web\Request;

class UrlHelper extends YiiUrl
{
    public const KEY_MODULE = 'module';
    public const KEY_CONTROLLER = 'controller';
    public const KEY_ACTION = 'action';
    public const KEY_PARAMS = 'params';

    public static function isCurrent($url, array $compare = null): bool
    {
        $compare = $compare !== null && count($compare) ? $compare : [
            self::KEY_MODULE,
            self::KEY_CONTROLLER,
            self::KEY_ACTION,
            self::KEY_PARAMS,
        ];

        $currentUrl = Yii::$app->request->getUrl();
        $resolveUrl = static::resolve($url);
        $resolveCurrentUrl = static::resolve($currentUrl);
        $failCompare = [];

        if (!$resolveUrl || !$resolveCurrentUrl) {
            return false;
        }

        $keys = [
            self::KEY_MODULE,
            self::KEY_CONTROLLER,
            self::KEY_ACTION,
        ];

        foreach ($keys as $key) {
            if (in_array($key, $compare, true)) {
                if ($resolveUrl[$key] !== $resolveCurrentUrl[$key]) {
                    return false;
                }

                if ($resolveUrl[$key] === null) {
                    $failCompare[] = $key;
                }
            }
        }

        if (in_array(static::KEY_PARAMS, $compare, true)) {
            $urlParams = ArrayHelper::getValue($resolveUrl, 'params');
            $currentUrlParams = ArrayHelper::getValue($resolveCurrentUrl, 'params');

            if (
                in_array(static::KEY_PARAMS, $compare, true)
                &&
                count(array_diff_assoc($urlParams, $currentUrlParams))
            ) {
                return false;
            }

            if (!count($urlParams)) {
                $failCompare[] = static::KEY_PARAMS;
            }
        }

        /** @noinspection IfReturnReturnSimplificationInspection */
        if (count($failCompare) === count($compare)) {
            return false;
        }

        return true;
    }

    public static function resolve($url): ?array
    {
        $controller = $actionId = null;
        $requestParams = [];

        try {
            $url = str_replace(Yii::$app->request->baseUrl, '', $url);

            $urlToRoute = self::toRoute($url);

            parse_str(parse_url($url, PHP_URL_QUERY), $urlParams);

            $urlPath = parse_url($urlToRoute, PHP_URL_PATH);
            $urlPath = rtrim($urlPath, '/');

            $request = new Request([
                'baseUrl' => Yii::$app->request->baseUrl,
                'url' => $urlPath,
                'queryParams' => $urlParams,
            ]);

            $requestData = $request->resolve();

            if (is_array($requestData)) {
                $requestUrl = $requestData[0] ?? null;
                $requestParams = $requestData[1] ?? null;

                /** @var \yii\web\Controller $controller */
                $controllerData = Yii::$app->createController($requestUrl);

                if (is_array($controllerData)) {
                    /** @noinspection OffsetOperationsInspection */
                    $controller = $controllerData[0] ?? null;

                    /** @noinspection OffsetOperationsInspection */
                    $actionId = $controllerData[1] ?? null;
                }
            }
        } /** @noinspection PhpUndefinedClassInspection */
        catch (Throwable $exception) {
            Yii::error("UrlHelper::resolve() error:\n\n" . ExceptionHelper::toString($exception));

            return null;
        }

        if (!$controller) {
            return null;
        }

        $controllerId = ArrayHelper::getValue($controller, 'id');
        $actionId = $actionId ?: $controller->defaultAction;
        $moduleId = $controller->module && !($controller->module instanceof Application)
            ? $controller->module->id
            : null;

        return [
            static::KEY_MODULE => $moduleId,
            static::KEY_CONTROLLER => $controllerId,
            static::KEY_ACTION => $actionId,
            static::KEY_PARAMS => $requestParams,
        ];
    }

    public static function getResolve($url, $key, $defaultValue = null)
    {
        $resolve = static::resolve($url);

        if (!$resolve) {
            return $defaultValue;
        }

        return ArrayHelper::getValue($resolve, $key, $defaultValue);
    }

    public static function getModuleId($url)
    {
        return static::getResolve($url, 0);
    }

    public static function getControllerId($url)
    {
        return static::getResolve($url, 1);
    }

    public static function getActionId($url)
    {
        return static::getResolve($url, 2);
    }

    public static function getParams($url)
    {
        return static::getResolve($url, 3, []);
    }

    public static function getParam($url, $key, $defaultValue = null)
    {
        return ArrayHelper::getValue(static::getParams($url), $key, $defaultValue);
    }

    public static function getRouteString($url)
    {
        $resolveUrl = static::resolve($url);

        if (!is_array($resolveUrl)) {
            return null;
        }

        $moduleId = $resolveUrl[0] ?? null;
        $controllerId = $resolveUrl[1] ?? null;
        $actionId = $resolveUrl[2] ?? null;

        return '@' . Yii::$app->id . '/' . ($moduleId ? $moduleId . '/' : '') . $controllerId . '/' . $actionId;
    }
}