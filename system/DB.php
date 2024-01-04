<?php

namespace Aurora\System;

final class DB
{
    private const DEFAULT_OPTIONS = [
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::MYSQL_ATTR_FOUND_ROWS => true,
    ];

    /**
     * Database connection
     * @var \PDO
     */
    public \PDO $connection;

    /**
     * DSN
     * @var string
     */
    public string $dsn;

    public function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = self::DEFAULT_OPTIONS)
    {
        $this->connection = new \PDO($dsn, $username, $password, $options);
        $this->dsn = $dsn;
    }

    /**
     * Runs the given query
     * @param string $sql the query
     * @param mixed ...$args the arguments
     * @return \PDOStatement|false the query result object
     */
    public function query(string $sql, ...$args): \PDOStatement|false
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($args);

        return $stmt;
    }

    /**
     * Inserts a row into the specified table
     * @param string $table the table
     * @param array $data the row data
     * @return string|false the id of the inserted row on success, false otherwise
     */
    public function insert(string $table, array $data): string|false
    {
        return $this->intoOperation('INSERT', $table, $data);
    }

    /**
     * Replaces a row into the specified table
     * @param string $table the table
     * @param array $data the row data
     * @return string|false the id of the replaced row on success, false otherwise
     */
    public function replace(string $table, array $data): string|false
    {
        return $this->intoOperation('REPLACE', $table, $data);
    }

    /**
     * Updates a row from the specified table
     * @param string $table the table
     * @param array $data the row data
     * @param int $id the row id
     * @return bool true on success, false otherwise
     */
    public function update(string $table, array $data, int $id): bool
    {
        $stmt = $this->connection->prepare("UPDATE $table
            SET " . implode(', ', array_map(fn($field) => "`$field` = ?", array_keys($data))) . "
            WHERE id = $id");
        return $stmt->execute(array_values($data)) && $stmt->rowCount() > 0;
    }

    /**
     * Deletes a row from the specified table
     * @param string $table the table
     * @param [mixed] $id the row id
     * @param [string] $column the column to be compared with the given id
     * @return bool true on success, false otherwise
     */
    public function delete(string $table, $id = null, string $column = 'id'): bool
    {
        return !isset($id)
            ? $this->connection->exec("DELETE FROM $table")
            : $this->connection->prepare("DELETE FROM $table WHERE `$column` = ?")->execute([ $id ]);
    }

    /**
     * Returns the number of rows in the specified table
     * @param string $table the table
     * @param [string] $where the condition for the count
     * @param [string] $group_by the group by for the count
     * @return int the number of rows
     */
    public function count(string $table, string $join = '', string $where = '', string $group_by = ''): int
    {
        $table = [ "SELECT $table.* FROM $table" ];

        if (!empty($join)) {
            $table[] = " $join";
        }

        if (!empty($where)) {
            $table[] = "WHERE $where";
        }

        if (!empty($group_by)) {
            $table[] = "GROUP BY $group_by";
        }

        return (int) $this->query('SELECT COUNT(*) AS total FROM (' . implode(' ', $table) . ') AS t')->fetch()['total'];
    }

    /**
     * Returns the given string escaped to be used in queries
     * @param string $str the string
     * @return string the string escaped
     */
    public function escape(string $str): string
    {
        return preg_replace("/[^A-Za-z0-9 \_\.\,\/\(\)]/", '', $str);
    }

    /**
     * Executes an INTO statement (insert or replace)
     * @param string $statement the statement (e.g., INSERT, REPLACE)
     * @param string $table the table
     * @param array $data the row data
     * @return string|false the id of the row on success, false otherwise
     */
    private function intoOperation(string $statement, string $table, array $data = []): string|false
    {
        $keys = array_map(fn($key) => "`$key`", array_keys($data));

        $res = $this->connection->prepare("$statement INTO $table
                (" . implode(', ', $keys) . ')
                VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')')
            ->execute(array_values($data));

        return $res ? $this->connection->lastInsertId() : false;
    }
}
