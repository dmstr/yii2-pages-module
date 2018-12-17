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
use ReflectionMethod;
use ReflectionParameter;
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
 * Hint:
 *
 * If the method as described above returns false, then this property will be ignored.
 *
 * If the method as described above returns true, then this property will be displayed. This functionality can be used to maniulate e.g. title or description (see class propertie `$allowedProperties`)
 *
 * You can use php doc block to add option to properties:
 *
 * Example:
 *
 * /**
 * * @editor title My Title
 * *\/
 * public function detailActionParamProductName() {
 *  return true;
 * }
 *
 * This will generate a input with defined title for an *existing* parameter
 *
 *
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


            // return json for json editor
            return $this->generateJson($actionRefl->getParameters(), $actionId);

        } catch (ReflectionException $e) {
            return '{}';
        }
    }


    /**
     * Generate json for request param json editor
     *
     * @param ReflectionParameter[] $parameters
     * @param string $actionId
     * @return string
     * @throws ReflectionException
     */
    private function generateJson($parameters, $actionId)
    {

        $properties = [];
        $requiredFields =[];
        $debug = [];
        foreach ($parameters as $parameter) {
            // get name
            $parameterName = $parameter->name;

            $debug[] = $parameterName;

            // nameActionParamId
            $methodName = $actionId . 'ActionParam' . ucfirst($parameterName);
            // use data from method if it exist.
            if ($this->hasMethod($methodName)) {
                $enumData = $this->$methodName();

                // hide field if method returns false
                if ($enumData === false) {
                    continue;
                }

                // instanciate reflection of this methods
                $methodRefl = new ReflectionMethod($this, $methodName);

                // get docs from method
                $docs = $methodRefl->getDocComment();
                $additionalData = [];
                if ($docs !== false) {
                    // matches e.g.
                    // @descrition My custom description
                    // in php doc blocks
                    preg_match_all('/@editor (\$[a-z]+) (.*)\n/', $docs, $matches);
                    if (isset($matches[1], $matches[2]) && \count($matches[1]) === \count($matches[2])) {
                        $matchIndex = 0;
                        foreach ($matches[1] as $propertyName) {
                            $additionalData[$propertyName] = $matches[2][$matchIndex];
                            $matchIndex++;
                        }
                    }
                }

                // additional properties from docs
                $extraProperties = [];

                // set title to auto gen title if not defined
                if (!isset($additionalData['title'])) {
                    $additionalData['title'] = Inflector::camel2words($parameterName);
                }
                // set type to string if not defined
                if (!isset($additionalData['type'])) {
                    $additionalData['type'] = 'string';
                }

                // add to required if not is optional
                if (!$parameter->isOptional()) {
                    $requiredFields[] = $parameterName;
                }

                foreach ($additionalData as $name => $value) {
                    // if value not is object or array
                    if (substr($value, 0, 2) !== '{"' && substr($value, 0, 1) !== '[' && substr($value, -1, 2) !== '"}' && substr($value, -1) !== ']') {
                        $value = '"' . $value . '"';
                    }
                    $extraProperties[] = '"' . $name . '": ' . $value;
                }

                if (!empty($extraProperties)) {
                    $extraProperties = implode(',', $extraProperties);
                } else {
                    $extraProperties = '';
                }

                if (\is_array($enumData)) {
                    $keys = $this->jsonListFromArray(array_keys($enumData));
                    $values = $this->jsonListFromArray($enumData);

                    $properties[] = <<<JSON
"{$parameterName}": {
      "enum": [{$keys}],
      {$extraProperties},
      "options": {
        "enum_titles": [{$values}]
      }
}
JSON;
                } else {
                    $properties[] = $this->defaultFieldJson($parameterName, $extraProperties);
                }

            } else {

                // add defaults here again to guarantee same behavior as if property would have a corresponding method
                $extraProperties = '"type": "string","title": "' . Inflector::camel2words($parameterName) . '"';

                if (!$parameter->isOptional()) {
                    $requiredFields[] = $parameterName;
                }

                // generate default if nothing else is defined
                $properties[] = $this->defaultFieldJson($parameterName, $extraProperties);
            }
        }

        $properties = implode(',' . PHP_EOL, $properties);

        $requiredProperties = '';
        if (!empty($requiredFields)) {
            $requiredProperties = '"required": [' . $this->jsonListFromArray($requiredFields) . '],';
        }

        // build json
        return <<<JSON
{
  "title": "Request Params",
  "type": "object",
  {$requiredProperties}
  "properties": {
    {$properties}
  }
}
JSON;

    }

    protected function jsonListFromArray($array) {
        return '"' . implode('","', $array) . '"';
    }

    protected function defaultFieldJson($parameterName, $extraProperties = [])
    {
        return <<<JSON
"{$parameterName}": {
      {$extraProperties}
}
JSON;
    }

}