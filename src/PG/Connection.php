<?php

namespace Lifetrenz\Transcendz\PG;

use Lifetrenz\Transcendz\Exception\InvalidPgConnectionDsn;
use PgSql\Connection as PgConnection;

class Connection
{
    private const POSTGRESQL_SCHEME = "postgresql";

    private string $scheme;

    private string $host;

    private int $port;

    private string $user;

    private ?string $password;

    private string $database;

    private ?string $serverVersion;

    private ?string $charset;

    private ?string $applicationName;

    private bool $isConnected = false;

    public function __construct(string $connectionDsn)
    {
        $this->scheme = parse_url($connectionDsn, PHP_URL_SCHEME);
        if ($this->scheme !== self::POSTGRESQL_SCHEME) {
            throw new InvalidPgConnectionDsn(
                sprintf(
                    "Given DSN %s is not qualified postgresql connection DSN",
                    $connectionDsn
                )
            );
        }
        $this->host = parse_url($connectionDsn, PHP_URL_HOST);
        $this->port = parse_url($connectionDsn, PHP_URL_PORT);
        $this->user = parse_url($connectionDsn, PHP_URL_USER);
        $this->password = urldecode(parse_url($connectionDsn, PHP_URL_PASS));
        $this->database = trim(parse_url($connectionDsn, PHP_URL_PATH), "/");
        parse_str(parse_url($connectionDsn, PHP_URL_QUERY), $queryparams);
        $this->serverVersion = $queryparams["serverVersion"] ?? null;
        $this->charset = $queryparams["charset"] ?? null;
        $this->applicationName = $queryparams["applicationName"] ?? null;
    }

    /**
     * Get the value of scheme
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * Get the value of host
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Get the value of port
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Get the value of user
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Get the value of password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Get the value of database
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * Get the value of serverVersion
     */
    public function getServerVersion(): ?string
    {
        return $this->serverVersion;
    }

    /**
     * Get the value of charset
     */
    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function connect(): PgConnection
    {
        $connectionParts = [
            sprintf("host=%s", $this->getHost()),
            sprintf("port=%s", $this->getPort()),
            sprintf("dbname=%s", $this->getDatabase()),
            sprintf("user=%s", $this->getUser()),
        ];

        $optionParts = [];

        if ($this->getPassword() !== null) {
            $connectionParts[] = sprintf("password=%s", $this->getPassword());
        }

        if ($this->getApplicationName() !== null) {
            $connectionParts[] = sprintf("application_name=%s", $this->getApplicationName());
        }

        if ($this->getCharset() !== null) {
            $optionParts[] = sprintf("--client_encoding=%s", $this->getCharset());
        }

        if (count($optionParts) > 0) {
            $options = sprintf("options='%s'", implode(" ", $optionParts));
        }

        $connectionString = implode(" ", $connectionParts) . $options ?? '';

        $connection = pg_connect($connectionString);

        if (
            !$connection ||
            pg_connection_status($connection) !== PGSQL_CONNECTION_OK ||
            !$connection instanceof PgConnection
        ) {
            throw new InvalidPgConnectionDsn("Not able to establish connection to database.");
        }

        $this->isConnected = true;
        return $connection;
    }

    /**
     * Get the value of isConnected
     */
    public function isConnected()
    {
        return $this->isConnected;
    }

    public function getApplicationName(): ?string
    {
        return $this->applicationName;
    }
}
