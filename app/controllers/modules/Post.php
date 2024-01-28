<?php

namespace Aurora\App\Modules;

final class Post extends \Aurora\App\ModuleBase
{
    protected string $select = 'posts.*,
        COUNT(DISTINCT views.ip) AS views,
        users.id AS user_id,
        users.name AS user_name,
        users.slug AS user_slug,
        users.image AS user_image,
        GROUP_CONCAT(tag_id, ",") AS tags_id';
    protected string $table = 'posts';
    protected string $join = 'LEFT JOIN users ON users.id = posts.user_id
        LEFT JOIN posts_to_tags p2t ON p2t.post_id = posts.id
        LEFT JOIN views ON views.type = "post" AND views.item_id = posts.id';
    protected string $group_by = 'posts.id';
    protected array $relations = [ 'posts_to_tags' => 'post_id' ];
    protected array $orders = [
        'title' => 'posts.title ASC, posts.id DESC',
        'author' => 'users.name ASC, posts.title ASC',
        'date' => 'posts.published_at DESC, posts.title ASC',
        'views' => 'views DESC, posts.title ASC',
    ];

    protected function getRowData($data): mixed
    {
        $data['tags'] = $this->getTags(empty($data['tags_id']) ? [] : explode(',', $data['tags_id']));
        return $data;
    }

    public function add(array $data): string|bool
    {
        try {
            $this->db->connection->beginTransaction();
            $id = $this->db->insert($this->table, $this->getBaseData($data));
            $this->setTags($id, $data['tags'] ?? []);
            $this->db->connection->commit();
        } catch (\PDOException $e) {
            $this->db->connection->rollBack();
            error_log($e->getMessage());
            return false;
        }

        return $id;
    }

    public function save(int $id, array $data): bool
    {
        try {
            $this->db->connection->beginTransaction();
            $res = $this->db->update($this->table, $this->getBaseData($data), $id);
            $this->setTags($id, $data['tags'] ?? []);
            $this->db->connection->commit();
        } catch (\PDOException $e) {
            $this->db->connection->rollBack();
            error_log($e->getMessage());
            return false;
        }

        return $res;
    }

    public function checkFields(array $data, $id): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = $this->language->get('invalid_value');
        }

        if (isset($data['slug']) && !empty($this->get([ 'slug' => $data['slug'], '!id' => $id ]))) {
            $errors['slug'] = $this->language->get('repeated_slug');
        }

        if (empty($data['slug'])) {
            $errors['slug'] = $this->language->get('invalid_value');
        }

        if (!\Aurora\App\Permission::can('edit_posts')) {
            http_response_code(403);
            $errors[0] = $this->language->get('no_permission');
        }

        if ($data['status'] && !\Aurora\App\Permission::can('publish_posts')) {
            $errors[0] = $this->language->get('published_posts_permission_error');
        }

        return $errors;
    }

    public function getCondition(array $filters): string
    {
        $where = [];

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = match (strval($filters['status'])) {
                '1' => 'posts.status AND posts.published_at <= ' . time(),
                '0' => 'posts.status = 0',
                'scheduled' => 'posts.status AND posts.published_at > ' . time(),
            };
        }

        if (isset($filters['user']) && $filters['user'] !== '') {
            $where[] = 'posts.user_id = ' . ((int) $filters['user']);
        }

        if (!empty($filters['search'])) {
            $search = $this->db->escape($filters['search']);
            $where[] = "(posts.title LIKE '%$search%' OR posts.description LIKE '%$search%')";
        }

        return implode(' AND ', $where);
    }

    private function getTags(array $ids): array
    {
        static $tags = null;

        if ($tags === null) {
            $tags = $this->db->query('SELECT * FROM tags')->fetchAll();
        }

        $result = [];
        foreach ($tags as $tag) {
            if (in_array($tag['id'], $ids)) {
                $result[$tag['slug']] = $tag['name'];
            }
        }

        natcasesort($result);

        return $result;
    }

    private function setTags(int $id, array $tags): void
    {
        $this->db->delete('posts_to_tags', $id, 'post_id');
        foreach ($tags as $tag) {
            $this->db->replace('posts_to_tags', [ 'post_id' => $id, 'tag_id' => $tag ]);
        }
    }

    private function getBaseData(array $data): array
    {
        return [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'html' => $data['html'],
            'user_id' => $data['user_id'],
            'image' => $data['image'] ?? null,
            'image_alt' => $data['image_alt'],
            'status' => $data['status'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'canonical_url' => $data['canonical_url'],
            'published_at' => (int) strtotime($data['published_at']),
        ];
    }
}
