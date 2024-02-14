<?php

namespace Lifetrenz\Transcendz\PG;

use Lifetrenz\Transcendz\Exception\InvalidPgConnectionDsn;

class Connection
{
    private const POSTGRESQL_SCHEME = "postgresql";

    private string $scheme;

    private string $host;

    private int $port;

    private string $user;

    private string $password;

    private string $database;

    private ?string $serverVersion;

    private ?string $charset;

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
        $this->password = parse_url($connectionDsn, PHP_URL_PASS);
        $this->database = trim(parse_url($connectionDsn, PHP_URL_PATH), "/");
        parse_str(parse_url($connectionDsn, PHP_URL_QUERY), $queryparams);
        $this->serverVersion = $queryparams["serverVersion"] ?? null;
        $this->charset = $queryparams["charset"] ?? null;
    }

    /**
     * Get the value of scheme
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get the value of host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the value of port
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get the value of database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get the value of serverVersion
     */
    public function getServerVersion()
    {
        return $this->serverVersion;
    }

    /**
     * Get the value of charset
     */
    public function getCharset()
    {
        return $this->charset;
    }

    public function connect()
    {
        $connection = pg_connect(sprintf(
            "host=%s port=%s dbname=%s user=%s password=%s",
            $this->host,
            $this->port,
            $this->database,
            $this->user,
            $this->password
        ));
        if (!$connection) {
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
}
