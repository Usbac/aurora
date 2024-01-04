<?php

namespace Aurora\App;

final class Migration
{
    private const TABLES = [
        'links' => [
            'id'     => 'INTEGER PRIMARY KEY',
            'title'  => 'TEXT',
            'url'    => 'TEXT',
            'order'  => 'INTEGER',
            'status' => 'INTEGER',
        ],
        'pages' => [
            'id'               => 'INTEGER PRIMARY KEY',
            'title'            => 'TEXT',
            'slug'             => 'VARCHAR(255) UNIQUE',
            'html'             => 'TEXT',
            'status'           => 'INTEGER',
            'static'           => 'INTEGER',
            'static_file'      => 'TEXT',
            'meta_title'       => 'TEXT',
            'meta_description' => 'TEXT',
            'canonical_url'    => 'TEXT',
            'edited_at'        => 'INTEGER',
        ],
        'password_restores' => [
            'user_id'    => 'INTEGER PRIMARY KEY',
            'hash'       => 'TEXT',
            'created_at' => 'INTEGER',
        ],
        'posts' => [
            'id'               => 'INTEGER PRIMARY KEY',
            'title'            => 'TEXT',
            'slug'             => 'VARCHAR(255) UNIQUE',
            'description'      => 'TEXT',
            'html'             => 'TEXT',
            'user_id'          => 'INTEGER',
            'image'            => 'TEXT',
            'image_alt'        => 'TEXT',
            'status'           => 'INTEGER',
            'meta_title'       => 'TEXT',
            'meta_description' => 'TEXT',
            'canonical_url'    => 'TEXT',
            'published_at'     => 'INTEGER',
        ],
        'posts_to_tags' => [
            'post_id' => 'INTEGER',
            'tag_id'  => 'INTEGER',
            ''        => 'CONSTRAINT posts_to_tags_pk UNIQUE (`post_id`, `tag_id`)',
        ],
        'roles' => [
            'level' => 'INTEGER PRIMARY KEY',
            'slug'  => 'VARCHAR(255)',
        ],
        'roles_permissions' => [
            'role_level' => 'INTEGER',
            'permission' => 'VARCHAR(255)',
            ''           => 'CONSTRAINT roles_permissions_pk UNIQUE (`role_level`, `permission`)',
        ],
        'settings' => [
            'key'   => 'VARCHAR(255) UNIQUE',
            'value' => 'TEXT',
        ],
        'tags' => [
            'id'               => 'INTEGER PRIMARY KEY',
            'name'             => 'TEXT',
            'slug'             => 'VARCHAR(255) UNIQUE',
            'description'      => 'TEXT',
            'meta_title'       => 'TEXT',
            'meta_description' => 'TEXT',
        ],
        'users' => [
            'id'          => 'INTEGER PRIMARY KEY',
            'name'        => 'TEXT',
            'slug'        => 'VARCHAR(255) UNIQUE',
            'email'       => 'VARCHAR(255) UNIQUE',
            'image'       => 'TEXT',
            'status'      => 'INTEGER',
            'password'    => 'TEXT',
            'role'        => 'INTEGER',
            'created_at'  => 'INTEGER',
            'last_active' => 'INTEGER',
        ],
        'views' => [
            'type'    => 'VARCHAR(255)',
            'item_id' => 'INTEGER',
            'ip'      => 'VARCHAR(255)',
            'date'    => 'INTEGER',
            ''        => 'CONSTRAINT views_pk UNIQUE (`type`, `item_id`, `ip`)',
        ],
    ];

    public function __construct(private $db)
    {
        $this->db = $db;
    }

    /**
     * Returns the database data
     * @return array the database data
     */
    public function export(): array
    {
        $data = [];
        foreach (array_keys(self::TABLES) as $table) {
            $data[$table] = $this->db->query("SELECT * FROM $table")->fetchAll();
        }

        return $data;
    }

    /**
     * Imports the database data
     * @param array $data the database data
     * @return bool true on success, false otherwise
     */
    public function import(array $data = []): bool
    {
        $success = false;

        try {
            $this->db->connection->beginTransaction();
            $this->createSchema();

            foreach ($data as $table => $rows) {
                foreach ($rows as $row) {
                    $columns = array_map(fn($column) => "`$column`", array_keys($row));

                    $this->db->query("INSERT INTO $table
                        (" . implode(', ', $columns) . ')
                        VALUES (' . implode(', ', array_fill(0, count($row), '?')) . ')', ...array_values($row));
                }
            }

            $success = $this->db->connection->commit();
        } catch (\PDOException $e) {
            $this->db->connection->rollBack();
            error_log($e->getMessage());
            $success = false;
        }

        return $success;
    }

    /**
     * Rebuilds the database schema by dropping all tables and recreating them
     */
    public function createSchema(): void
    {
        foreach (self::TABLES as $table => $structure) {
            $this->db->query("DROP TABLE IF EXISTS `$table`");
            $columns = [];
            foreach ($structure as $column => $type) {
                $columns[] = empty($column) ? $type : "`$column` $type";
            }

            $this->db->query("CREATE TABLE $table (" . implode(', ', $columns) . ')');
        }
    }
}
