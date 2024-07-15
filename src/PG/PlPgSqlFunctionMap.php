<?php

namespace Lifetrenz\Transcendz\PG;

use DateTime;
use Lifetrenz\Transcendz\Exception\InvalidPlPgSqlClass;
use Lifetrenz\Transcendz\PG\Attribute\DatasetCollection;
use Lifetrenz\Transcendz\PG\Attribute\OutputField;
use Lifetrenz\Transcendz\PG\Attribute\PlPgSql;
use ReflectionClass;
use ReflectionProperty;

class PlPgSqlFunctionMap
{
    private Connection $connection;

    private string $query;

    private string $resultType;

    private bool $hasSetOptions = false;

    private string $setOptions = "";

    public function __construct(
        private PlPgSqlDataSetFunction | PlPgSqlDataRecordFunction | PlPgSqlScalarDataFunction $plPgSqlFunctionObject,
        private ConnectionRegistry $registry
    ) {
        $plPgSqlReflection = new ReflectionClass($this->plPgSqlFunctionObject);
        $plpgsqlAttributeClass = $plPgSqlReflection->getAttributes(PlPgSql::class)[0] ?? null;
        if ($plpgsqlAttributeClass === null) {
            throw new InvalidPlPgSqlClass(sprintf(
                'Cannot build query from object, no PlPgSql attribute declared on class %s',
                $this->plPgSqlFunctionObject::class
            ));
        }
        $plpgSqlObject = $plpgsqlAttributeClass->newInstance();
        $this->connection = new Connection($this->registry->getConnection($plpgSqlObject->getDbIdentifier()));
        $this->query = QueryBuilder::getFunctionCallQuery($plPgSqlFunctionObject);
        $this->hasSetOptions = $plpgSqlObject->getSetOptions() !== null;
        if ($this->hasSetOptions) {
            $this->setOptions = QueryBuilder::getFunctionSetOptions($plPgSqlFunctionObject);
        }
        $this->setResultType($plPgSqlReflection);
    }

    private function setResultType(ReflectionClass $plPgSqlReflection)
    {
        $resultReflectionProperties = $plPgSqlReflection->getProperties();
        $plPgSqlInterfaces = $plPgSqlReflection->getInterfaceNames();

        if (in_array(PlPgSqlDataSetFunction::class, $plPgSqlInterfaces)) {
            $resultReflectionProperty = array_reduce(
                $resultReflectionProperties,
                function ($resultReflectionProperty, ReflectionProperty $property) {
                    if ($property->getName() === "resultDataSetDTO") {
                        $resultReflectionProperty = $property;
                    }
                    return $resultReflectionProperty;
                }
            );
            if ($resultReflectionProperty === null) {
                throw new InvalidPlPgSqlClass(sprintf(
                    'Cannot build query from object, no resultDTO property declared in class %s',
                    $plPgSqlReflection->getName()
                ));
            }
            $datasetCollectionAttribute = $resultReflectionProperty->getAttributes(DatasetCollection::class)[0] ?? null;
            if ($datasetCollectionAttribute === null) {
                throw new InvalidPlPgSqlClass(sprintf(
                    'Cannot build query from object, no DatasetCollection attribute declared in class %s on resultDTO property',
                    $plPgSqlReflection->getName()
                ));
            }
            $datasetCollectionObject = $datasetCollectionAttribute->newInstance();
            if ($datasetCollectionObject instanceof DatasetCollection) {
                $this->resultType = $datasetCollectionObject->getDtoClassName();
            }
        }
        if (in_array(PlPgSqlScalarDataFunction::class, $plPgSqlInterfaces)) {
            $resultReflectionProperty = array_reduce(
                $resultReflectionProperties,
                function ($resultReflectionProperty, ReflectionProperty $property) {
                    if ($property->getName() === "result") {
                        $resultReflectionProperty = $property;
                    }
                    return $resultReflectionProperty;
                }
            );
            if ($resultReflectionProperty === null) {
                throw new InvalidPlPgSqlClass(sprintf(
                    'Cannot build query from object, no result property declared in class %s',
                    $plPgSqlReflection->getName()
                ));
            }
            $this->resultType = $resultReflectionProperty->getType()->getName();
        }

        if (in_array(PlPgSqlDataRecordFunction::class, $plPgSqlInterfaces)) {
            $resultReflectionProperty = array_reduce(
                $resultReflectionProperties,
                function ($resultReflectionProperty, ReflectionProperty $property) {
                    if ($property->getName() === "resultDataRecordDTO") {
                        $resultReflectionProperty = $property;
                    }
                    return $resultReflectionProperty;
                }
            );
            if ($resultReflectionProperty === null) {
                throw new InvalidPlPgSqlClass(sprintf(
                    'Cannot build query from object, no resultDataRecordDTO property declared in class %s',
                    $plPgSqlReflection->getName()
                ));
            }
            $this->resultType = $resultReflectionProperty->getType()->getName();
        }
    }

    public function mapResultSet(array $result): array
    {
        return array_map(fn ($row) => $this->getMappedObject($row), $result);
    }

    private function getMappedObject(array $row)
    {
        $dtoReflection = new ReflectionClass($this->resultType);
        $dto = $dtoReflection->newInstanceWithoutConstructor();
        foreach ($dtoReflection->getProperties() as $resultDtoClassProperty) {
            $outputFieldAttribute = $resultDtoClassProperty->getAttributes(OutputField::class)[0] ?? null;
            if ($outputFieldAttribute !== null) {
                $fieldObject = $outputFieldAttribute->newInstance();
                $resultDtoClassProperty->setValue($dto, self::convertValue($row[$fieldObject->getName()] ?? null, $fieldObject->getType()));
            }
        }
        return $dto;
    }

    public function getScalarResult(array $result): mixed
    {
        foreach ($result as $row) {
            foreach ($row as $field) {
                return $this->resultType === "bool" ? $field === "t" : $field;
            }
        }
    }

    private static function convertValue(mixed $value, DataType $type)
    {
        if ($value === null) {
            return null;
        }

        if ($type === DataType::JSON) {
            return json_decode($value, true);
        }

        if ($type === DataType::DATE || $type === DataType::TIMESTAMP || $type === DataType::TIMESTAMPTZ) {
            return new DateTime($value);
        }

        if ($type === DataType::BOOLEAN) {
            return $value === "t";
        }

        if ($type === DataType::INTEGER_ARRAY || $type === DataType::BIG_INTEGER_ARRAY || $type === DataType::VARCHAR_ARRAY) {
            return json_decode(str_replace(['{', '}'], ['[', ']'], $value));
        }

        return $value;
    }

    /**
     * Get the value of connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the value of query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the value of hasSetOptions
     */
    public function hasSetOptions()
    {
        return $this->hasSetOptions;
    }

    /**
     * Get the value of setOptions
     */
    public function getSetOptions()
    {
        return $this->setOptions;
    }
}
