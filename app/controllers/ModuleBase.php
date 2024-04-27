<?php

namespace Aurora\App;

class ModuleBase
{
    /**
     * SELECT statement
     * @var string
     */
    protected string $select = '*';

    /**
     * Table
     * @var string
     */
    protected string $table = '';

    /**
     * JOIN statement
     * @var string
     */
    protected string $join = '';

    /**
     * GROUP BY statement
     * @var string
     */
    protected string $group_by = '';

    /**
     * Relation with other tables columns
     * @var array
     */
    protected array $relations = [];

    /**
     * ORDER BY statements
     * @var array
     */
    protected array $orders = [];

    public function __construct(protected $db = null, protected $language = null)
    {
        $this->db = $db;
        $this->language = $language;
    }

    /**
     * Returns the row based on the given search
     * @param array $search the search fields to find the row
     * Must follow the format field => value
     * Fields that start with a exclamation character (!) will be compared as non-equal
     * Fields that are numeric will be added as is to the where condition in the query
     * @return mixed the row
     */
    public function get(array $search)
    {
        $where = $values = [];

        foreach ($search as $key => $val) {
            if (is_numeric($key)) {
                if (!empty($val)) {
                    $where[] = $val;
                }

                continue;
            }

            $compare = '=';

            if ($key[0] == '!') {
                $compare = '!=';
                $key = mb_substr($key, 1);
            }

            $where[] = $this->table . ".$key $compare ?";
            $values[] = $val;
        }

        $data = $this->db->query($this->getBaseQuery(implode(' AND ', $where)), ...$values)->fetch();

        return $data ? $this->getRowData($data) : $data;
    }

    /**
     * Returns the page
     * @param [int] $page the page
     * @param [int] $per_page the number of items per page
     * @param [string] $where the where condition for the query
     * @param [string] $order the order by condition for the query
     * @param [bool] $return_all_til_page return all elements til the given page or not
     * @return mixed the rows in the page
     */
    public function getPage(
        ?int $page = null,
        ?int $per_page = null,
        ?string $where = '',
        ?string $order = '',
        bool $return_all_til_page = false
        ): array
    {
        if (empty($order)) {
            $order = array_key_first($this->orders);
        }

        $base_query = $this->getBaseQuery($where) . ' ORDER BY ' . ($this->orders[$order] ?? '1');

        if (!isset($page)) {
            $res = $this->db->query($base_query);
        } else {
            $res = $return_all_til_page
                ? $this->db->query("$base_query LIMIT ?", $page * $per_page)
                : $this->db->query("$base_query LIMIT ? OFFSET ?", $per_page, ($page - 1) * $per_page);
        }

        return array_map(fn($row) => $this->getRowData($row), $res->fetchAll());
    }

    /**
     * Returns the number of rows
     * @param [string] $where the condition for the count
     * @return int the number of rows
     */
    public function count(string $where = '')
    {
        return $this->db->count($this->table, $this->join, $where, $this->group_by);
    }

    /**
     * Returns true if a next page is available, false otherwise
     * Used for pagination
     * @param [int] $page the page
     * @param [int] $per_page the number of items per page
     * @param [string] $where the where condition for the query
     * @return bool true if a next page is available, false otherwise
     */
    public function isNextPageAvailable(int $page, int $per_page, string $where = ''): bool
    {
        return $this->db->count($this->table, $this->join, $where, $this->group_by) > ($page * $per_page);
    }

    /**
     * Deletes the rows with the given ids
     * @param array $ids the row ids
     * @return bool true if the rows have been deleted, false otherwise
     */
    public function remove(array $ids): bool
    {
        try {
            $this->db->connection->beginTransaction();

            foreach ($ids as $id) {
                $this->db->delete($this->table, $id);
                foreach ($this->relations as $table => $field) {
                    $this->db->delete($table, $id, $field);
                }
            }

            $success = $this->db->connection->commit();
        } catch (\PDOException $e) {
            $this->db->connection->rollBack();
            error_log($e->getMessage());
            return false;
        }

        return $success;
    }

    /**
     * Returns the row with additional data mapped into it
     * @param mixed $data the row
     * @return mixed the row with additional data
     */
    protected function getRowData($data): mixed
    {
        return $data;
    }

    /**
     * Returns the base query
     * @param [string] $where the where condition for the query
     * @return string the base query
     */
    private function getBaseQuery(string $where = ''): string
    {
        $query = [ 'SELECT ' . $this->select . ' FROM ' . $this->table ];

        if (!empty($this->join)) {
            $query[] = $this->join;
        }

        if (!empty($where)) {
            $query[] = "WHERE $where";
        }

        if (!empty($this->group_by)) {
            $query[] = 'GROUP BY ' . $this->group_by;
        }

        return implode(' ', $query);
    }
}
