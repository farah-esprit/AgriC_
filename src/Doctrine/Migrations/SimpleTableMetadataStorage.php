<?php

declare(strict_types=1);

namespace App\Doctrine\Migrations;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\Metadata\ExecutedMigration;
use Doctrine\Migrations\Metadata\ExecutedMigrationsList;
use Doctrine\Migrations\Metadata\Storage\MetadataStorage;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\Query\Query;
use Doctrine\Migrations\Version\AlphabeticalComparator;
use Doctrine\Migrations\Version\Comparator;
use Doctrine\Migrations\Version\Direction;
use Doctrine\Migrations\Version\ExecutionResult;
use Doctrine\Migrations\Version\Version;

/**
 * Stockage des versions exécutées sans comparaison de schéma DBAL (évite une boucle
 * "metadata storage not up to date" sur MySQL + DBAL 4 lorsque la table existe déjà).
 */
final class SimpleTableMetadataStorage implements MetadataStorage
{
    private readonly Comparator $comparator;

    public function __construct(
        private readonly Connection $connection,
        private readonly TableMetadataStorageConfiguration $configuration = new TableMetadataStorageConfiguration(),
    ) {
        $this->comparator = new AlphabeticalComparator();
    }

    public function ensureInitialized(): void
    {
        $sm = $this->connection->createSchemaManager();
        if ($sm->tablesExist([$this->configuration->getTableName()])) {
            return;
        }

        $table = $this->configuration->getTableName();
        $vCol  = $this->configuration->getVersionColumnName();
        $eCol  = $this->configuration->getExecutedAtColumnName();
        $tCol  = $this->configuration->getExecutionTimeColumnName();

        if ($this->connection->getDatabasePlatform() instanceof SQLitePlatform) {
            $this->connection->executeStatement(sprintf(
                'CREATE TABLE %s (%s VARCHAR(%d) NOT NULL PRIMARY KEY, %s DATETIME DEFAULT NULL, %s INTEGER DEFAULT NULL)',
                $table,
                $vCol,
                $this->configuration->getVersionColumnLength(),
                $eCol,
                $tCol
            ));
        } else {
            $len = $this->configuration->getVersionColumnLength();
            $this->connection->executeStatement(sprintf(
                'CREATE TABLE %s (%s VARCHAR(%d) NOT NULL, %s DATETIME DEFAULT NULL, %s INT DEFAULT NULL, PRIMARY KEY(%s))
                 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB',
                $table,
                $vCol,
                $len,
                $eCol,
                $tCol,
                $vCol
            ));
        }
    }

    public function getExecutedMigrations(): ExecutedMigrationsList
    {
        $this->ensureInitialized();

        $sm = $this->connection->createSchemaManager();
        if (! $sm->tablesExist([$this->configuration->getTableName()])) {
            return new ExecutedMigrationsList([]);
        }

        $table = $this->configuration->getTableName();
        $rows  = $this->connection->fetchAllAssociative(sprintf('SELECT * FROM %s', $table));
        $platform = $this->connection->getDatabasePlatform();

        $migrations = [];
        foreach ($rows as $row) {
            $row = array_change_key_case($row, CASE_LOWER);

            $version = new Version($row[strtolower($this->configuration->getVersionColumnName())]);

            $executedAtRaw = $row[strtolower($this->configuration->getExecutedAtColumnName())] ?? '';
            $executedAt    = $executedAtRaw !== ''
                ? DateTimeImmutable::createFromFormat($platform->getDateTimeFormatString(), (string) $executedAtRaw)
                : null;

            $executionTime = isset($row[strtolower($this->configuration->getExecutionTimeColumnName())])
                ? (float) $row[strtolower($this->configuration->getExecutionTimeColumnName())] / 1000
                : null;

            $migrations[(string) $version] = new ExecutedMigration(
                $version,
                $executedAt instanceof DateTimeImmutable ? $executedAt : null,
                $executionTime,
            );
        }

        uasort(
            $migrations,
            fn (ExecutedMigration $a, ExecutedMigration $b): int => $this->comparator->compare($a->getVersion(), $b->getVersion()),
        );

        return new ExecutedMigrationsList($migrations);
    }

    public function complete(ExecutionResult $result): void
    {
        $this->ensureInitialized();
        $table = $this->configuration->getTableName();

        if ($result->getDirection() === Direction::DOWN) {
            $this->connection->delete($table, [
                $this->configuration->getVersionColumnName() => (string) $result->getVersion(),
            ]);

            return;
        }

        $this->connection->insert($table, [
            $this->configuration->getVersionColumnName() => (string) $result->getVersion(),
            $this->configuration->getExecutedAtColumnName() => $result->getExecutedAt(),
            $this->configuration->getExecutionTimeColumnName() => $result->getTime() === null ? null : (int) round($result->getTime() * 1000),
        ], [
            Types::STRING,
            Types::DATETIME_IMMUTABLE,
            Types::INTEGER,
        ]);
    }

    public function reset(): void
    {
        $this->ensureInitialized();
        $this->connection->executeStatement(
            sprintf('DELETE FROM %s WHERE 1 = 1', $this->configuration->getTableName()),
        );
    }

    /** @return iterable<Query> */
    public function getSql(ExecutionResult $result): iterable
    {
        $table = $this->configuration->getTableName();
        $vCol  = $this->configuration->getVersionColumnName();
        $eCol  = $this->configuration->getExecutedAtColumnName();
        $tCol  = $this->configuration->getExecutionTimeColumnName();

        yield new Query('-- Version ' . (string) $result->getVersion() . ' update table metadata');

        if ($result->getDirection() === Direction::DOWN) {
            yield new Query(sprintf(
                'DELETE FROM %s WHERE %s = %s',
                $table,
                $vCol,
                $this->connection->quote((string) $result->getVersion()),
            ));

            return;
        }

        yield new Query(sprintf(
            'INSERT INTO %s (%s, %s, %s) VALUES (%s, %s, 0)',
            $table,
            $vCol,
            $eCol,
            $tCol,
            $this->connection->quote((string) $result->getVersion()),
            $this->connection->quote(($result->getExecutedAt() ?? new DateTimeImmutable())->format('Y-m-d H:i:s')),
        ));
    }
}
