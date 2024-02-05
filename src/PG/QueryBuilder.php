<?php

namespace Lifetrenz\Transcendz\PG;

use DateTime;
use InvalidArgumentException;
use Lifetrenz\Transcendz\PG\Attribute\DatasetCollection;
use Lifetrenz\Transcendz\PG\Attribute\InputArgument;
use Lifetrenz\Transcendz\PG\Attribute\OutputField;
use Lifetrenz\Transcendz\PG\Attribute\PlPgSql;
use Lifetrenz\Transcendz\Exception\InvalidPlPgSqlClass;
use ReflectionClass;

class QueryBuilder
{
    public static function getFunctionCallQuery(
        PlPgSqlDataSetFunction | PlPgSqlDataRecordFunction | PlPgSqlScalarDataFunction $plpgsqlDAO
    ): string {
        $plPgSqlReflection = new ReflectionClass($plpgsqlDAO);
        $plpgsqlAttributeClass = $plPgSqlReflection->getAttributes(PlPgSql::class)[0] ?? null;
        if ($plpgsqlAttributeClass === null) {
            throw new InvalidPlPgSqlClass(sprintf(
                'Cannot build query from object, no PlPgSql attribute declared on class %s',
                $plpgsqlDAO::class
            ));
        }
        $plpgSqlObject = $plpgsqlAttributeClass->newInstance();
        $functionSchema = $plpgSqlObject->getSchema() ?? 'public';
        $functionName = $plpgSqlObject->getName();
        if ($plpgsqlDAO instanceof PlPgSqlDataSetFunction) {
            $fields = self::getDataSetOutputFields($plPgSqlReflection);
        } elseif ($plpgsqlDAO instanceof PlPgSqlDataRecordFunction) {
            $fields = self::getDataRecordOutputFields($plPgSqlReflection);
        } elseif ($plpgsqlDAO instanceof PlPgSqlScalarDataFunction) {
            $fields = self::getDataScalarResultField($plPgSqlReflection);
        }
        $arguments = self::getInputArguments($plPgSqlReflection, $plpgsqlDAO);
        return sprintf(
            'SELECT %s FROM "%s".%s (%s);',
            implode(', ', $fields),
            $functionSchema,
            $functionName,
            implode(', ', $arguments),
        );
    }

    private static function getInputArguments(ReflectionClass $plPgSqlReflection, object $plpgsqlDAO): array
    {
        $arguments = [];
        foreach ($plPgSqlReflection->getProperties() as $plPgSqlReflectionProperty) {
            $inputArgumentAttribute = $plPgSqlReflectionProperty->getAttributes(InputArgument::class)[0] ?? null;
            if ($inputArgumentAttribute === null) {
                continue;
            }
            $inputArgumentObject = $inputArgumentAttribute->newInstance();
            if ($inputArgumentObject instanceof InputArgument) {
                $argumentName = $inputArgumentObject->getName();
                $argumentDataType = $inputArgumentObject->getType();
                $argumentValue = $plPgSqlReflectionProperty->getValue($plpgsqlDAO);
                $argumentDefaultValue = $plPgSqlReflectionProperty->getDefaultValue();
                $arguments[] = sprintf(
                    '%s => %s',
                    $argumentName,
                    self::getTypedPgValue($argumentValue, $argumentDefaultValue, $argumentDataType)
                );
            }
        }
        return $arguments;
    }

    private static function getDataSetOutputFields(ReflectionClass $plPgSqlReflection): array
    {
        $resultDtoReflectionProperty = $plPgSqlReflection->getProperty("resultDataSetDTO");
        $datasetCollectionAttribute = $resultDtoReflectionProperty->getAttributes(DatasetCollection::class)[0] ?? null;
        if ($datasetCollectionAttribute === null) {
            throw new InvalidPlPgSqlClass(sprintf(
                'Cannot build query from object, no DatasetCollection attribute declared in class %s on resultDataSetDTO property',
                $plPgSqlReflection->getName()
            ));
        }
        $fields = [];
        $datasetCollectionObject = $datasetCollectionAttribute->newInstance();
        if ($datasetCollectionObject instanceof DatasetCollection) {
            $resultDtoClass = $datasetCollectionObject->getDtoClassName();
            $resultDtoClassProperties = (new ReflectionClass($resultDtoClass))->getProperties();
            foreach ($resultDtoClassProperties as $resultDtoClassProperty) {
                $outputFieldAttribute = $resultDtoClassProperty->getAttributes(OutputField::class)[0] ?? null;
                if ($outputFieldAttribute !== null) {
                    $fields[] = $outputFieldAttribute->newInstance()->getName();
                }
            }
        }
        return $fields;
    }

    private static function getDataRecordOutputFields(ReflectionClass $plPgSqlReflection): array
    {
        $resultDtoReflectionProperty = $plPgSqlReflection->getProperty("resultDataRecordDTO");
        $resultDataRecordClass = $resultDtoReflectionProperty->getType()->getName();
        $fields = [];
        $resultDtoClassProperties = (new ReflectionClass($resultDataRecordClass))->getProperties();
        foreach ($resultDtoClassProperties as $resultDtoClassProperty) {
            $outputFieldAttribute = $resultDtoClassProperty->getAttributes(OutputField::class)[0] ?? null;
            if ($outputFieldAttribute !== null) {
                $fields[] = $outputFieldAttribute->newInstance()->getName();
            }
        }
        return $fields;
    }

    private static function getDataScalarResultField(ReflectionClass $plPgSqlReflection): array
    {
        $resultDtoReflectionProperty = $plPgSqlReflection->getProperty("result");
        $outputFieldAttribute = $resultDtoReflectionProperty->getAttributes(OutputField::class)[0] ?? null;
        if ($outputFieldAttribute === null) {
            throw new InvalidPlPgSqlClass(sprintf(
                'Cannot build query from object, no OutputField attribute declared in class %s on result property',
                $plPgSqlReflection->getName()
            ));
        }
        $fields[] = $outputFieldAttribute->newInstance()->getName();
        return $fields;
    }

    private static function getTypedPgValue(mixed $value, mixed $defaultValue, DataType $type): string
    {
        if ($value === null && $defaultValue === null) {
            return "NULL::" . $type->value;
        }

        $data = $value ?? $defaultValue;
        if ($type === DataType::JSON) {
            $jsonData = json_encode($data);
            if (!$jsonData) {
                return "NULL::" . $type->value;
            }
            return "'" . str_replace("'", "''", $jsonData) . "'::" . $type->value;
        }

        if ($type === DataType::INTEGER_ARRAY || $type === DataType::BIG_INTEGER_ARRAY) {
            if (!is_array($data)) {
                throw new InvalidArgumentException(sprintf("Array expected but found %s!", gettype($data)));
            }
            return "ARRAY[" . implode(", ", $data) . "]::" . $type->value;
        }

        if ($type === DataType::DATE || $type === DataType::TIMESTAMPTZ || $type === DataType::TIMESTAMPTZ) {
            if (! $data instanceof DateTime) {
                throw new InvalidArgumentException(sprintf("DateTime expected but found %s!", gettype($data)));
            }
            return "'" . date_format($data, "Y-m-d H:i:s") . "'::" . $type->value;
        }

        if ($type === DataType::BOOLEAN) {
            if ($data) {
                return "'" . "TRUE" . "'::" . $type->value;
            } else {
                return "'" . "FALSE" . "'::" . $type->value;
            }
        }

        return "'" . str_replace("'", "''", $data) . "'::" . $type->value;
    }
}
