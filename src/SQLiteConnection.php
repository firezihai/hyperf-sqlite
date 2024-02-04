<?php

declare(strict_types=1);

namespace Firezihai\Sqlite;

use App\Core\Sqlite\Query\Grammars\SQLiteGrammar;
use Closure;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver as SQLiteDriver;
use Exception;
use Firezihai\Sqlite\Query\Grammars\SQLiteGrammar as QueryGrammar;
use Firezihai\Sqlite\Query\SQLiteProcessor;
use Firezihai\Sqlite\Schema\Grammars\SQLiteGrammar as SchemaGrammar;
use Firezihai\Sqlite\Schema\SQLiteBuilder;
use Hyperf\Database\Connection;
use PDO;

class SQLiteConnection extends Connection
{
    /**
     * Create a new database connection instance.
     *
     * @param Closure|PDO $pdo
     * @param string $database
     * @param string $tablePrefix
     */
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);

        $enableForeignKeyConstraints = $this->getForeignKeyConstraintsConfigurationValue();

        if ($enableForeignKeyConstraints === null) {
            return;
        }

        $enableForeignKeyConstraints
        ? $this->getSchemaBuilder()->enableForeignKeyConstraints()
        : $this->getSchemaBuilder()->disableForeignKeyConstraints();
    }

    /**
     * Get a schema builder instance for the connection.
     */
    public function getSchemaBuilder(): SQLiteBuilder
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new SQLiteBuilder($this);
    }

    /**
     * Escape a binary value for safe SQL embedding.
     *
     * @param string $value
     * @return string
     */
    protected function escapeBinary($value)
    {
        $hex = bin2hex($value);

        return "x'{$hex}'";
    }

    /**
     * Determine if the given database exception was caused by a unique constraint violation.
     *
     * @return bool
     */
    protected function isUniqueConstraintError(Exception $exception)
    {
        return boolval(preg_match('#(column(s)? .* (is|are) not unique|UNIQUE constraint failed: .*)#i', $exception->getMessage()));
    }

    /**
     * Get the default query grammar instance.
     *
     * @return SQLiteGrammar
     */
    protected function getDefaultQueryGrammar(): QueryGrammar
    {
        ($grammar = new QueryGrammar())->setConnection($this);

        return $this->withTablePrefix($grammar);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \App\Core\Sqlite\Schema\Grammars\SQLiteGrammar
     */
    protected function getDefaultSchemaGrammar(): SchemaGrammar
    {
        ($grammar = new SchemaGrammar())->setConnection($this);

        return $this->withTablePrefix($grammar);
    }

    /**
     * Get the default post processor instance.
     */
    protected function getDefaultPostProcessor(): SQLiteProcessor
    {
        return new SQLiteProcessor();
    }

    /**
     * Get the Doctrine DBAL driver.
     */
    protected function getDoctrineDriver(): SQLiteDriver
    {
        return new SQLiteDriver();
    }

    /**
     * Get the database connection foreign key constraints configuration option.
     *
     * @return null|bool
     */
    protected function getForeignKeyConstraintsConfigurationValue()
    {
        return $this->getConfig('foreign_key_constraints');
    }
}
