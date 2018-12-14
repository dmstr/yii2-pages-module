<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2018 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\traits;


use ReflectionClass;
use ReflectionException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * This trait will enable auto fetching request params to append matching JSON for request param's json editor
 *
 * USUAGE / HOW IT WORKS:
 *
 * To enable for a specific controller, use this trait in the desired controller
 *
 * By default it will generate a text field per action parameter.
 *
 * For customization you can create a public method for each individual action parameter by adding a method which name have to follow this schema:
 *
 * `camelizedActionId` + ActionParam + `ParameterName`
 *
 * camelizedActionId: Action Id camelized with the first letter lowercased.
 * ParameterName: name of parameter variable with the first letter uppercased.
 *
 * Example: detailActionParamProductId
 *
 * This method must return a array (key-value pairs), where the keys should refer to the actual value and the value will be the label
 *
 * Example:
 *
 * return ArrayHelper::map(Product::find()->all(),'id','name');
 *
 *
 * @package dmstr\modules\pages\traits
 * @author Elias Luhr <e.luhr@herzogkommunikation.de>
 */
trait RequestParamActionTrait
{


    // get json by route
    public function jsonFromAction($route)
    {
        try {
            // get action id from route
            $actionId = lcfirst(Inflector::camelize(basename($route)));

            // get potential action name in controller
            $actionName = 'action' . Inflector::camelize($actionId);

            // get reflection class instance of controller
            $controllerRefl = new ReflectionClass(static::class);

            // get method reflection of action. If not exist exception will be thrown an catched underneath
            $actionRefl = $controllerRefl->getMethod($actionName);

            // map parameter names to key value paired array
            $parameterNames = ArrayHelper::map($actionRefl->getParameters(), 'name', 'name');

            // return json for json editor
            return $this->generateJson($parameterNames, $actionId);

        } catch (ReflectionException $e) {
            return '{}';
        }
    }


    /**
     * Generate json for request param json editor
    */
    private function generateJson($parameters, $actionId)
    {

        $properties = [];
        foreach ($parameters as $parameterName) {

            // title for property in json
            $title = Inflector::camel2words($parameterName);

            // nameActionParamId
            $methodName = $actionId . 'ActionParam' . ucfirst($parameterName);

            // use data from method if it exist.
            if ($this->hasMethod($methodName)) {
                $enumData = $this->$methodName();

                $keys = '"' . implode('","', array_keys($enumData)) . '"';
                $values = '"' . implode('","', $enumData) . '"';

                $properties[] = <<<JSON
"{$parameterName}": {
      "type": "string",
      "enum": [{$keys}],
      "title": "{$title}",
      "options": {
        "enum_titles": [{$values}]
      }
}
JSON;
            } else {
                // generate default if nothing else is defined
                $properties[] = <<<JSON
"{$parameterName}": {
      "type": "string",
      "title": "{$title}"
}
JSON;
            }
        }


        $properties = implode(',' . PHP_EOL, $properties);
        // build json
        return <<<JSON
{
  "title": "Request Params",
  "type": "object",
  "properties": {
    $properties
  }
}
JSON;

    }

}