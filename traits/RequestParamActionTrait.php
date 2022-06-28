<?php
/**
 * @link http://www.diemeisterei.de/
 * @copyright Copyright (c) 2018 diemeisterei GmbH, Stuttgart
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dmstr\modules\pages\traits;


use dmstr\modules\pages\helpers\PageHelper;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
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
 * For customization you can create a public method for each individual action parameter by adding a method which name
 * have to follow this schema:
 *
 * `camelizedActionId` + ActionParam + `ParameterName`
 *
 * camelizedActionId: Action Id camelized with the first letter lowercased.
 * ParameterName: name of parameter variable with the first letter uppercased.
 *
 * Example: detailActionParamProductId
 *
 * This method must return a array (key-value pairs), where the keys should refer to the actual value and the value will
 * be the label
 *
 * Example:
 *
 * return ArrayHelper::map(Product::find()->all(),'id','name');
 *
 *
 * Hints:
 *
 * - If the method as described above returns false, then this property will be ignored.
 *
 * - If the method as described above returns true, then this property will be displayed. This functionality can be used
 *   to manipulate e.g. title or description (see class property `$allowedProperties`)
 *
 * - You can use php doc block to add options to properties:
 *
 *   Example:
 *
 *   /**
 *    * @editor title My Title
 *   *\/
 *   public function detailActionParamProductName() {
 *     return true;
 *   }
 *
 *   This will generate a input with defined title for an *existing* parameter
 *
 * - If property is NOT optional, it will be set as required in json schema.
 *   However, since this only implies that the property must be set in the data, but not that a value must also be set,
 *   a validation rule should be defined using notations (see above). For properties of type 'string' a minLength: 1
 *   option is set as fallback.
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
            return PageHelper::defaultJsonSchema();
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

        $requiredFields = [];

        // init main json struct object with defaults
        $jsonStruct = new \stdClass();
        $jsonStruct->title = 'Request Params';
        $jsonStruct->type = "object";
        $jsonStruct->properties = [];

        foreach ($parameters as $parameter) {
            // get name
            $parameterName = $parameter->name;

            // init obj for each property and set defaults
            $paramStruct = new \stdClass();
            $paramStruct->title = Inflector::camel2words($parameterName);
            $paramStruct->type = 'string';

            // nameActionParamId
            $methodName = $actionId . 'ActionParam' . ucfirst($parameterName);
            // use data from method if it exists
            if ($this->hasMethod($methodName)) {
                $enumData = $this->$methodName();

                // hide field if method returns false
                if ($enumData === false) {
                    continue;
                }

                // instantiate reflection of the actionParam method to be able to get (optional) docBlock
                $methodRefl = new ReflectionMethod($this, $methodName);

                // get docs from actionParam method
                $docs = $methodRefl->getDocComment();
                $additionalData = [];
                if ($docs !== false) {
                    // matches e.g.
                    // @editor description My custom description
                    // in php doc blocks
                    preg_match_all('/@editor[\s]+([a-zA-Z-_]+)[\s]+(.*)\n/', $docs, $matches);
                    if (isset($matches[1], $matches[2]) && \count($matches[1]) === \count($matches[2])) {
                        $matchIndex = 0;
                        foreach ($matches[1] as $propertyName) {
                            $additionalData[$propertyName] = $matches[2][$matchIndex];
                            $matchIndex++;
                        }
                    }
                }

                foreach ($additionalData as $name => $value) {
                    // if value looks like json object or array, get struct from json string
                    if ((substr($value, 0, 1) === '[' && substr($value, -1) !== ']') || (substr($value, 0, 2) === '{"'  && substr($value, -1, 2) === '"}') ) {
                        $value = json_decode($value);
                    }
                    $paramStruct->$name = $value;
                }

                if (\is_array($enumData)) {
                    // if we want string, cast keys to string, otherwise we would get IDs as int
                    if ($paramStruct->type === 'string') {
                        $paramStruct->enum = array_map('strval', array_keys($enumData));
                    } else {
                        $paramStruct->enum = array_keys($enumData);
                    }

                    // ensure options is set...
                    if (!isset($paramStruct->options)) {
                        $paramStruct->options = [];
                    }
                    // ... and add enum_titles, ensure strings
                    $paramStruct->options['enum_titles'] = array_map('strval', array_values($enumData));
                }

            }

            // add to required if param is not optional
            if (!$parameter->isOptional()) {
                $requiredFields[] = $parameterName;
                // TODO: how to check other types?
                if (($paramStruct->type === 'string') && (!isset($paramStruct->minLength))) {
                    $paramStruct->minLength = 1;
                }
            }

            $jsonStruct->properties[$parameterName] = $paramStruct;

        }

        if (!empty($requiredFields)) {
            $jsonStruct->required = $requiredFields;
        }

        return json_encode($jsonStruct);

    }

}