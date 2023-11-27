<?php

namespace Lifetrenz\Transcendz\Converter;

use Lifetrenz\Transcendz\Attribute\JsonBody;
use Lifetrenz\Transcendz\Attribute\QueryParams;
use Lifetrenz\Transcendz\Attribute\TextBody;
use ReflectionClass;

class ControllerArgumentResolver
{
    public static function resolve(
        object|string $classObject,
        string $methodName,
        array $argumentList,
        ?string $bodyContent = null,
        array $queryParams = []
    ): array {
        $controllerClassReflection = new ReflectionClass($classObject);
        $controllerMethodReflection = $controllerClassReflection->getMethod($methodName);
        $controllerMethodArguments = $controllerMethodReflection->getParameters();
        foreach ($controllerMethodArguments as $controllerArgument) {
            $jsonBodyAttribute = $controllerArgument->getAttributes(JsonBody::class)[0] ?? null;
            if ($jsonBodyAttribute !== null) {
                $reqObject = $bodyContent === null ?
                    null :
                    JsonToObject::convert(
                        $bodyContent,
                        $controllerArgument->getType()->getName()
                    );
                $argumentList = array_map(
                    fn ($argument) =>
                    is_object($argument) && get_class($argument) === $controllerArgument->getType()->getName() ? $reqObject : $argument,
                    $argumentList
                );
            }
            $queryParamAttribute = $controllerArgument->getAttributes(QueryParams::class)[0] ?? null;
            if ($queryParamAttribute !== null) {
                $queryParamsObject = $queryParams === [] ? null : ArrayToObject::convert($queryParams, $controllerArgument->getType()->getName());
                $argumentList = array_map(
                    fn ($argument) =>
                    is_object($argument) && get_class($argument) === $controllerArgument->getType()->getName() ? $queryParamsObject : $argument,
                    $argumentList
                );
            }
            $textBodyAttribute = $controllerArgument->getAttributes(TextBody::class)[0] ?? null;
            if ($textBodyAttribute !== null) {
                $argumentList = array_map(
                    fn ($argument) => is_string($argument) ? $bodyContent : $argument,
                    $argumentList
                );
            }
        }
        return $argumentList;
    }
}
