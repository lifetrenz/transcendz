<?php

namespace Lifetrenz\Transcendz\PG;

enum DataType: string
{
    case VARCHAR = 'VARCHAR';

    case TEXT = 'TEXT';

    case NUMERIC = 'NUMERIC';

    case INTEGER = 'INTEGER';

    case BIG_INTEGER = 'BIGINT';

    case DATE = 'DATE';

    case TIME = 'TIME';

    case TIMESTAMP = 'TIMESTAMP';

    case TIMESTAMPTZ = 'TIMESTAMPTZ';

    case INTERVAL = 'INTERVAL';

    case BOOLEAN = 'BOOLEAN';

    case BIT = 'BIT';

    case JSON = 'JSON';

    case INTEGER_ARRAY = 'INTEGER[]';

    case BIG_INTEGER_ARRAY = 'BIGINT[]';
}
