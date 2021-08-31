<?php
// @formatter:off

/**
 * A helper file for Laravel, to provide autocomplete information to your IDE
 * Generated for Laravel 8.53.1.
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 * @see https://github.com/barryvdh/laravel-ide-helper
 */
    namespace Illuminate\Support\Facades {

            /**
     *
     *
     * @see \Illuminate\Database\DatabaseManager
     * @see \Illuminate\Database\Connection
     */
        class DB {
                    /**
         * Get a database connection instance.
         *
         * @param string|null $name
         * @return \Illuminate\Database\Connection
         * @static
         */
        public static function connection($name = null)
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        return $instance->connection($name);
        }
                    /**
         * Disconnect from the given database and remove from local cache.
         *
         * @param string|null $name
         * @return void
         * @static
         */
        public static function purge($name = null)
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        $instance->purge($name);
        }
                    /**
         * Disconnect from the given database.
         *
         * @param string|null $name
         * @return void
         * @static
         */
        public static function disconnect($name = null)
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        $instance->disconnect($name);
        }
                    /**
         * Reconnect to the given database.
         *
         * @param string|null $name
         * @return \Illuminate\Database\Connection
         * @static
         */
        public static function reconnect($name = null)
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        return $instance->reconnect($name);
        }
                    /**
         * Set the default database connection for the callback execution.
         *
         * @param string $name
         * @param callable $callback
         * @return mixed
         * @static
         */
        public static function usingConnection($name, $callback)
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        return $instance->usingConnection($name, $callback);
        }
                    /**
         * Get the default connection name.
         *
         * @return string
         * @static
         */
        public static function getDefaultConnection()
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        return $instance->getDefaultConnection();
        }
                    /**
         * Set the default connection name.
         *
         * @param string $name
         * @return void
         * @static
         */
        public static function setDefaultConnection($name)
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        $instance->setDefaultConnection($name);
        }
                    /**
         * Get all of the support drivers.
         *
         * @return array
         * @static
         */
        public static function supportedDrivers()
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        return $instance->supportedDrivers();
        }
                    /**
         * Get all of the drivers that are actually available.
         *
         * @return array
         * @static
         */
        public static function availableDrivers()
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        return $instance->availableDrivers();
        }
                    /**
         * Register an extension connection resolver.
         *
         * @param string $name
         * @param callable $resolver
         * @return void
         * @static
         */
        public static function extend($name, $resolver)
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        $instance->extend($name, $resolver);
        }
                    /**
         * Return all of the created connections.
         *
         * @return array
         * @static
         */
        public static function getConnections()
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        return $instance->getConnections();
        }
                    /**
         * Set the database reconnector callback.
         *
         * @param callable $reconnector
         * @return void
         * @static
         */
        public static function setReconnector($reconnector)
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        $instance->setReconnector($reconnector);
        }
                    /**
         * Set the application instance used by the manager.
         *
         * @param \Illuminate\Contracts\Foundation\Application $app
         * @return \Illuminate\Database\DatabaseManager
         * @static
         */
        public static function setApplication($app)
        {
                        /** @var \Illuminate\Database\DatabaseManager $instance */
                        return $instance->setApplication($app);
        }
                    /**
         * Determine if the connected database is a MariaDB database.
         *
         * @return bool
         * @static
         */
        public static function isMaria()
        {
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->isMaria();
        }
                    /**
         * Get a schema builder instance for the connection.
         *
         * @return \Illuminate\Database\Schema\MySqlBuilder
         * @static
         */
        public static function getSchemaBuilder()
        {
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getSchemaBuilder();
        }
                    /**
         * Get the schema state for the connection.
         *
         * @param \Illuminate\Filesystem\Filesystem|null $files
         * @param callable|null $processFactory
         * @return \Illuminate\Database\Schema\MySqlSchemaState
         * @static
         */
        public static function getSchemaState($files = null, $processFactory = null)
        {
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getSchemaState($files, $processFactory);
        }
                    /**
         * Set the query grammar to the default implementation.
         *
         * @return void
         * @static
         */
        public static function useDefaultQueryGrammar()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->useDefaultQueryGrammar();
        }
                    /**
         * Set the schema grammar to the default implementation.
         *
         * @return void
         * @static
         */
        public static function useDefaultSchemaGrammar()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->useDefaultSchemaGrammar();
        }
                    /**
         * Set the query post processor to the default implementation.
         *
         * @return void
         * @static
         */
        public static function useDefaultPostProcessor()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->useDefaultPostProcessor();
        }
                    /**
         * Begin a fluent query against a database table.
         *
         * @param \Closure|\Illuminate\Database\Query\Builder|string $table
         * @param string|null $as
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function table($table, $as = null)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->table($table, $as);
        }
                    /**
         * Get a new query builder instance.
         *
         * @return \Illuminate\Database\Query\Builder
         * @static
         */
        public static function query()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->query();
        }
                    /**
         * Run a select statement and return a single result.
         *
         * @param string $query
         * @param array $bindings
         * @param bool $useReadPdo
         * @return mixed
         * @static
         */
        public static function selectOne($query, $bindings = [], $useReadPdo = true)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->selectOne($query, $bindings, $useReadPdo);
        }
                    /**
         * Run a select statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return array
         * @static
         */
        public static function selectFromWriteConnection($query, $bindings = [])
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->selectFromWriteConnection($query, $bindings);
        }
                    /**
         * Run a select statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @param bool $useReadPdo
         * @return array
         * @static
         */
        public static function select($query, $bindings = [], $useReadPdo = true)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->select($query, $bindings, $useReadPdo);
        }
                    /**
         * Run a select statement against the database and returns a generator.
         *
         * @param string $query
         * @param array $bindings
         * @param bool $useReadPdo
         * @return \Generator
         * @static
         */
        public static function cursor($query, $bindings = [], $useReadPdo = true)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->cursor($query, $bindings, $useReadPdo);
        }
                    /**
         * Run an insert statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return bool
         * @static
         */
        public static function insert($query, $bindings = [])
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->insert($query, $bindings);
        }
                    /**
         * Run an update statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return int
         * @static
         */
        public static function update($query, $bindings = [])
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->update($query, $bindings);
        }
                    /**
         * Run a delete statement against the database.
         *
         * @param string $query
         * @param array $bindings
         * @return int
         * @static
         */
        public static function delete($query, $bindings = [])
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->delete($query, $bindings);
        }
                    /**
         * Execute an SQL statement and return the boolean result.
         *
         * @param string $query
         * @param array $bindings
         * @return bool
         * @static
         */
        public static function statement($query, $bindings = [])
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->statement($query, $bindings);
        }
                    /**
         * Run an SQL statement and get the number of rows affected.
         *
         * @param string $query
         * @param array $bindings
         * @return int
         * @static
         */
        public static function affectingStatement($query, $bindings = [])
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->affectingStatement($query, $bindings);
        }
                    /**
         * Run a raw, unprepared query against the PDO connection.
         *
         * @param string $query
         * @return bool
         * @static
         */
        public static function unprepared($query)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->unprepared($query);
        }
                    /**
         * Execute the given callback in "dry run" mode.
         *
         * @param \Closure $callback
         * @return array
         * @static
         */
        public static function pretend($callback)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->pretend($callback);
        }
                    /**
         * Bind values to their parameters in the given statement.
         *
         * @param \PDOStatement $statement
         * @param array $bindings
         * @return void
         * @static
         */
        public static function bindValues($statement, $bindings)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->bindValues($statement, $bindings);
        }
                    /**
         * Prepare the query bindings for execution.
         *
         * @param array $bindings
         * @return array
         * @static
         */
        public static function prepareBindings($bindings)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->prepareBindings($bindings);
        }
                    /**
         * Log a query in the connection's query log.
         *
         * @param string $query
         * @param array $bindings
         * @param float|null $time
         * @return void
         * @static
         */
        public static function logQuery($query, $bindings, $time = null)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->logQuery($query, $bindings, $time);
        }
                    /**
         * Register a database query listener with the connection.
         *
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function listen($callback)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->listen($callback);
        }
                    /**
         * Get a new raw query expression.
         *
         * @param mixed $value
         * @return \Illuminate\Database\Query\Expression
         * @static
         */
        public static function raw($value)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->raw($value);
        }
                    /**
         * Determine if the database connection has modified any database records.
         *
         * @return bool
         * @static
         */
        public static function hasModifiedRecords()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->hasModifiedRecords();
        }
                    /**
         * Indicate if any records have been modified.
         *
         * @param bool $value
         * @return void
         * @static
         */
        public static function recordsHaveBeenModified($value = true)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->recordsHaveBeenModified($value);
        }
                    /**
         * Set the record modification state.
         *
         * @param bool $value
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setRecordModificationState($value)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setRecordModificationState($value);
        }
                    /**
         * Reset the record modification state.
         *
         * @return void
         * @static
         */
        public static function forgetRecordModificationState()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->forgetRecordModificationState();
        }
                    /**
         * Indicate that the connection should use the write PDO connection for reads.
         *
         * @param bool $value
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function useWriteConnectionWhenReading($value = true)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->useWriteConnectionWhenReading($value);
        }
                    /**
         * Is Doctrine available?
         *
         * @return bool
         * @static
         */
        public static function isDoctrineAvailable()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->isDoctrineAvailable();
        }
                    /**
         * Get a Doctrine Schema Column instance.
         *
         * @param string $table
         * @param string $column
         * @return \Doctrine\DBAL\Schema\Column
         * @static
         */
        public static function getDoctrineColumn($table, $column)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getDoctrineColumn($table, $column);
        }
                    /**
         * Get the Doctrine DBAL schema manager for the connection.
         *
         * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
         * @static
         */
        public static function getDoctrineSchemaManager()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getDoctrineSchemaManager();
        }
                    /**
         * Get the Doctrine DBAL database connection instance.
         *
         * @return \Doctrine\DBAL\Connection
         * @static
         */
        public static function getDoctrineConnection()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getDoctrineConnection();
        }
                    /**
         * Get the current PDO connection.
         *
         * @return \PDO
         * @static
         */
        public static function getPdo()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getPdo();
        }
                    /**
         * Get the current PDO connection parameter without executing any reconnect logic.
         *
         * @return \PDO|\Closure|null
         * @static
         */
        public static function getRawPdo()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getRawPdo();
        }
                    /**
         * Get the current PDO connection used for reading.
         *
         * @return \PDO
         * @static
         */
        public static function getReadPdo()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getReadPdo();
        }
                    /**
         * Get the current read PDO connection parameter without executing any reconnect logic.
         *
         * @return \PDO|\Closure|null
         * @static
         */
        public static function getRawReadPdo()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getRawReadPdo();
        }
                    /**
         * Set the PDO connection.
         *
         * @param \PDO|\Closure|null $pdo
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setPdo($pdo)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setPdo($pdo);
        }
                    /**
         * Set the PDO connection used for reading.
         *
         * @param \PDO|\Closure|null $pdo
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setReadPdo($pdo)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setReadPdo($pdo);
        }
                    /**
         * Get the database connection name.
         *
         * @return string|null
         * @static
         */
        public static function getName()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getName();
        }
                    /**
         * Get the database connection full name.
         *
         * @return string|null
         * @static
         */
        public static function getNameWithReadWriteType()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getNameWithReadWriteType();
        }
                    /**
         * Get an option from the configuration options.
         *
         * @param string|null $option
         * @return mixed
         * @static
         */
        public static function getConfig($option = null)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getConfig($option);
        }
                    /**
         * Get the PDO driver name.
         *
         * @return string
         * @static
         */
        public static function getDriverName()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getDriverName();
        }
                    /**
         * Get the query grammar used by the connection.
         *
         * @return \Illuminate\Database\Query\Grammars\Grammar
         * @static
         */
        public static function getQueryGrammar()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getQueryGrammar();
        }
                    /**
         * Set the query grammar used by the connection.
         *
         * @param \Illuminate\Database\Query\Grammars\Grammar $grammar
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setQueryGrammar($grammar)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setQueryGrammar($grammar);
        }
                    /**
         * Get the schema grammar used by the connection.
         *
         * @return \Illuminate\Database\Schema\Grammars\Grammar
         * @static
         */
        public static function getSchemaGrammar()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getSchemaGrammar();
        }
                    /**
         * Set the schema grammar used by the connection.
         *
         * @param \Illuminate\Database\Schema\Grammars\Grammar $grammar
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setSchemaGrammar($grammar)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setSchemaGrammar($grammar);
        }
                    /**
         * Get the query post processor used by the connection.
         *
         * @return \Illuminate\Database\Query\Processors\Processor
         * @static
         */
        public static function getPostProcessor()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getPostProcessor();
        }
                    /**
         * Set the query post processor used by the connection.
         *
         * @param \Illuminate\Database\Query\Processors\Processor $processor
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setPostProcessor($processor)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setPostProcessor($processor);
        }
                    /**
         * Get the event dispatcher used by the connection.
         *
         * @return \Illuminate\Contracts\Events\Dispatcher
         * @static
         */
        public static function getEventDispatcher()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getEventDispatcher();
        }
                    /**
         * Set the event dispatcher instance on the connection.
         *
         * @param \Illuminate\Contracts\Events\Dispatcher $events
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setEventDispatcher($events)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setEventDispatcher($events);
        }
                    /**
         * Unset the event dispatcher for this connection.
         *
         * @return void
         * @static
         */
        public static function unsetEventDispatcher()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->unsetEventDispatcher();
        }
                    /**
         * Set the transaction manager instance on the connection.
         *
         * @param \Illuminate\Database\DatabaseTransactionsManager $manager
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setTransactionManager($manager)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setTransactionManager($manager);
        }
                    /**
         * Unset the transaction manager for this connection.
         *
         * @return void
         * @static
         */
        public static function unsetTransactionManager()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->unsetTransactionManager();
        }
                    /**
         * Determine if the connection is in a "dry run".
         *
         * @return bool
         * @static
         */
        public static function pretending()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->pretending();
        }
                    /**
         * Get the connection query log.
         *
         * @return array
         * @static
         */
        public static function getQueryLog()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getQueryLog();
        }
                    /**
         * Clear the query log.
         *
         * @return void
         * @static
         */
        public static function flushQueryLog()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->flushQueryLog();
        }
                    /**
         * Enable the query log on the connection.
         *
         * @return void
         * @static
         */
        public static function enableQueryLog()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->enableQueryLog();
        }
                    /**
         * Disable the query log on the connection.
         *
         * @return void
         * @static
         */
        public static function disableQueryLog()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->disableQueryLog();
        }
                    /**
         * Determine whether we're logging queries.
         *
         * @return bool
         * @static
         */
        public static function logging()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->logging();
        }
                    /**
         * Get the name of the connected database.
         *
         * @return string
         * @static
         */
        public static function getDatabaseName()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getDatabaseName();
        }
                    /**
         * Set the name of the connected database.
         *
         * @param string $database
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setDatabaseName($database)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setDatabaseName($database);
        }
                    /**
         * Set the read / write type of the connection.
         *
         * @param string|null $readWriteType
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setReadWriteType($readWriteType)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setReadWriteType($readWriteType);
        }
                    /**
         * Get the table prefix for the connection.
         *
         * @return string
         * @static
         */
        public static function getTablePrefix()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->getTablePrefix();
        }
                    /**
         * Set the table prefix in use by the connection.
         *
         * @param string $prefix
         * @return \Illuminate\Database\MySqlConnection
         * @static
         */
        public static function setTablePrefix($prefix)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->setTablePrefix($prefix);
        }
                    /**
         * Set the table prefix and return the grammar.
         *
         * @param \Illuminate\Database\Grammar $grammar
         * @return \Illuminate\Database\Grammar
         * @static
         */
        public static function withTablePrefix($grammar)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->withTablePrefix($grammar);
        }
                    /**
         * Register a connection resolver.
         *
         * @param string $driver
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function resolverFor($driver, $callback)
        {            //Method inherited from \Illuminate\Database\Connection
                        \Illuminate\Database\MySqlConnection::resolverFor($driver, $callback);
        }
                    /**
         * Get the connection resolver for the given driver.
         *
         * @param string $driver
         * @return mixed
         * @static
         */
        public static function getResolver($driver)
        {            //Method inherited from \Illuminate\Database\Connection
                        return \Illuminate\Database\MySqlConnection::getResolver($driver);
        }
                    /**
         * Execute a Closure within a transaction.
         *
         * @param \Closure $callback
         * @param int $attempts
         * @return mixed
         * @throws \Throwable
         * @static
         */
        public static function transaction($callback, $attempts = 1)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->transaction($callback, $attempts);
        }
                    /**
         * Start a new database transaction.
         *
         * @return void
         * @throws \Throwable
         * @static
         */
        public static function beginTransaction()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->beginTransaction();
        }
                    /**
         * Commit the active database transaction.
         *
         * @return void
         * @throws \Throwable
         * @static
         */
        public static function commit()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->commit();
        }
                    /**
         * Rollback the active database transaction.
         *
         * @param int|null $toLevel
         * @return void
         * @throws \Throwable
         * @static
         */
        public static function rollBack($toLevel = null)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->rollBack($toLevel);
        }
                    /**
         * Get the number of active transactions.
         *
         * @return int
         * @static
         */
        public static function transactionLevel()
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        return $instance->transactionLevel();
        }
                    /**
         * Execute the callback after a transaction commits.
         *
         * @param callable $callback
         * @return void
         * @throws \RuntimeException
         * @static
         */
        public static function afterCommit($callback)
        {            //Method inherited from \Illuminate\Database\Connection
                        /** @var \Illuminate\Database\MySqlConnection $instance */
                        $instance->afterCommit($callback);
        }

    }

            /**
     *
     *
     * @see \Illuminate\Database\Schema\Builder
     */
        class Schema {
                    /**
         * Create a database in the schema.
         *
         * @param string $name
         * @return bool
         * @static
         */
        public static function createDatabase($name)
        {
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->createDatabase($name);
        }
                    /**
         * Drop a database from the schema if the database exists.
         *
         * @param string $name
         * @return bool
         * @static
         */
        public static function dropDatabaseIfExists($name)
        {
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->dropDatabaseIfExists($name);
        }
                    /**
         * Determine if the given table exists.
         *
         * @param string $table
         * @return bool
         * @static
         */
        public static function hasTable($table)
        {
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->hasTable($table);
        }
                    /**
         * Get the column listing for a given table.
         *
         * @param string $table
         * @return array
         * @static
         */
        public static function getColumnListing($table)
        {
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->getColumnListing($table);
        }
                    /**
         * Drop all tables from the database.
         *
         * @return void
         * @static
         */
        public static function dropAllTables()
        {
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->dropAllTables();
        }
                    /**
         * Drop all views from the database.
         *
         * @return void
         * @static
         */
        public static function dropAllViews()
        {
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->dropAllViews();
        }
                    /**
         * Get all of the table names for the database.
         *
         * @return array
         * @static
         */
        public static function getAllTables()
        {
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->getAllTables();
        }
                    /**
         * Get all of the view names for the database.
         *
         * @return array
         * @static
         */
        public static function getAllViews()
        {
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->getAllViews();
        }
                    /**
         * Set the default string length for migrations.
         *
         * @param int $length
         * @return void
         * @static
         */
        public static function defaultStringLength($length)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        \Illuminate\Database\Schema\MySqlBuilder::defaultStringLength($length);
        }
                    /**
         * Set the default morph key type for migrations.
         *
         * @param string $type
         * @return void
         * @throws \InvalidArgumentException
         * @static
         */
        public static function defaultMorphKeyType($type)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        \Illuminate\Database\Schema\MySqlBuilder::defaultMorphKeyType($type);
        }
                    /**
         * Set the default morph key type for migrations to UUIDs.
         *
         * @return void
         * @static
         */
        public static function morphUsingUuids()
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        \Illuminate\Database\Schema\MySqlBuilder::morphUsingUuids();
        }
                    /**
         * Determine if the given table has a given column.
         *
         * @param string $table
         * @param string $column
         * @return bool
         * @static
         */
        public static function hasColumn($table, $column)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->hasColumn($table, $column);
        }
                    /**
         * Determine if the given table has given columns.
         *
         * @param string $table
         * @param array $columns
         * @return bool
         * @static
         */
        public static function hasColumns($table, $columns)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->hasColumns($table, $columns);
        }
                    /**
         * Get the data type for the given column name.
         *
         * @param string $table
         * @param string $column
         * @return string
         * @static
         */
        public static function getColumnType($table, $column)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->getColumnType($table, $column);
        }
                    /**
         * Modify a table on the schema.
         *
         * @param string $table
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function table($table, $callback)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->table($table, $callback);
        }
                    /**
         * Create a new table on the schema.
         *
         * @param string $table
         * @param \Closure $callback
         * @return void
         * @static
         */
        public static function create($table, $callback)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->create($table, $callback);
        }
                    /**
         * Drop a table from the schema.
         *
         * @param string $table
         * @return void
         * @static
         */
        public static function drop($table)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->drop($table);
        }
                    /**
         * Drop a table from the schema if it exists.
         *
         * @param string $table
         * @return void
         * @static
         */
        public static function dropIfExists($table)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->dropIfExists($table);
        }
                    /**
         * Drop columns from a table schema.
         *
         * @param string $table
         * @param string|array $columns
         * @return void
         * @static
         */
        public static function dropColumns($table, $columns)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->dropColumns($table, $columns);
        }
                    /**
         * Drop all types from the database.
         *
         * @return void
         * @throws \LogicException
         * @static
         */
        public static function dropAllTypes()
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->dropAllTypes();
        }
                    /**
         * Rename a table on the schema.
         *
         * @param string $from
         * @param string $to
         * @return void
         * @static
         */
        public static function rename($from, $to)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->rename($from, $to);
        }
                    /**
         * Enable foreign key constraints.
         *
         * @return bool
         * @static
         */
        public static function enableForeignKeyConstraints()
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->enableForeignKeyConstraints();
        }
                    /**
         * Disable foreign key constraints.
         *
         * @return bool
         * @static
         */
        public static function disableForeignKeyConstraints()
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->disableForeignKeyConstraints();
        }
                    /**
         * Register a custom Doctrine mapping type.
         *
         * @param string $class
         * @param string $name
         * @param string $type
         * @return void
         * @throws \Doctrine\DBAL\DBALException
         * @throws \RuntimeException
         * @static
         */
        public static function registerCustomDoctrineType($class, $name, $type)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->registerCustomDoctrineType($class, $name, $type);
        }
                    /**
         * Get the database connection instance.
         *
         * @return \Illuminate\Database\Connection
         * @static
         */
        public static function getConnection()
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->getConnection();
        }
                    /**
         * Set the database connection instance.
         *
         * @param \Illuminate\Database\Connection $connection
         * @return \Illuminate\Database\Schema\MySqlBuilder
         * @static
         */
        public static function setConnection($connection)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        return $instance->setConnection($connection);
        }
                    /**
         * Set the Schema Blueprint resolver callback.
         *
         * @param \Closure $resolver
         * @return void
         * @static
         */
        public static function blueprintResolver($resolver)
        {            //Method inherited from \Illuminate\Database\Schema\Builder
                        /** @var \Illuminate\Database\Schema\MySqlBuilder $instance */
                        $instance->blueprintResolver($resolver);
        }

    }
}


namespace  {
            class DB extends \Illuminate\Support\Facades\DB {}
            class Schema extends \Illuminate\Support\Facades\Schema {}
            class Str extends \Illuminate\Support\Str {}

}




