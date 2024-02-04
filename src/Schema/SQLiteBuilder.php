<?php

declare(strict_types=1);

namespace Firezihai\Sqlite\Schema;

use Hyperf\Database\Exception\QueryException;
use Hyperf\Database\Schema\Builder;
use Hyperf\Support\Filesystem\Filesystem;

use function Hyperf\Support\make;

class SQLiteBuilder extends Builder
{
    /**
     * Create a database in the schema.
     *
     * @param string $name
     * @return bool
     */
    public function createDatabase($name)
    {
        /**
         * @var Filesystem $fileSystem
         */
        $fileSystem = make(Filesystem::class);
        return $fileSystem->put($name, '') !== false;
    }

    /**
     * Drop a database from the schema if the database exists.
     *
     * @param string $name
     * @return bool
     */
    public function dropDatabaseIfExists($name)
    {
        /**
         * @var Filesystem $fileSystem
         */
        $fileSystem = make(Filesystem::class);
        return $fileSystem->exists($name)
        ? $fileSystem->delete($name)
        : true;
    }

    /**
     * Get the tables for the database.
     *
     * @return array
     */
    public function getTables()
    {
        $withSize = false;

        try {
            $withSize = $this->connection->scalar($this->grammar->compileDbstatExists());
        } catch (QueryException $e) {
        }

        return $this->connection->getPostProcessor()->processTables(
            $this->connection->selectFromWriteConnection($this->grammar->compileTables($withSize))
        );
    }

    /**
     * Get all of the table names for the database.
     *
     * @deprecated will be removed in a future Laravel version
     */
    public function getAllTables(): array
    {
        return $this->connection->select(
            $this->grammar->compileGetAllTables()
        );
    }

    /**
     * Get all of the view names for the database.
     *
     * @deprecated will be removed in a future Laravel version
     */
    public function getAllViews(): array
    {
        return $this->connection->select(
            $this->grammar->compileGetAllViews()
        );
    }

    /**
     * Drop all tables from the database.
     */
    public function dropAllTables(): void
    {
        if ($this->connection->getDatabaseName() !== ':memory:') {
            $this->refreshDatabaseFile();
        } else {
            $this->connection->select($this->grammar->compileEnableWriteableSchema());

            $this->connection->select($this->grammar->compileDropAllTables());

            $this->connection->select($this->grammar->compileDisableWriteableSchema());

            $this->connection->select($this->grammar->compileRebuild());
        }
    }

    /**
     * Drop all views from the database.
     */
    public function dropAllViews(): void
    {
        $this->connection->select($this->grammar->compileEnableWriteableSchema());

        $this->connection->select($this->grammar->compileDropAllViews());

        $this->connection->select($this->grammar->compileDisableWriteableSchema());

        $this->connection->select($this->grammar->compileRebuild());
    }

    /**
     * Empty the database file.
     */
    public function refreshDatabaseFile(): void
    {
        file_put_contents($this->connection->getDatabaseName(), '');
    }
}
