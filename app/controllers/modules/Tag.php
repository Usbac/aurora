<?php

namespace Aurora\App\Modules;

final class Tag extends \Aurora\App\ModuleBase
{
    protected string $select = 'tags.*, COUNT(post_id) AS posts';
    protected string $table = 'tags';
    protected string $join = 'LEFT JOIN posts_to_tags p2t ON p2t.tag_id = tags.id AND p2t.post_id IN (SELECT posts.id FROM posts)';
    protected string $group_by = 'tags.id';
    protected array $relations = [ 'posts_to_tags' => 'tag_id' ];
    protected array $orders = [
        'name' => 'tags.name DESC, tags.id DESC',
        'posts' => 'COUNT(post_id) DESC, tags.name ASC',
    ];

    public function add(array $data): string|false
    {
        return $this->db->insert($this->table, $this->getBaseData($data));
    }

    public function save(int $id, array $data): bool
    {
        return $this->db->update($this->table, $this->getBaseData($data), $id) ? $id : false;
    }

    public function checkFields(array $data, $id): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = $this->language->get('invalid_value');
        }

        if (!empty($data['slug']) &&
            !empty($this->get([ 'slug' => $data['slug'], '!id' => $id ]))) {
            $errors['slug'] = $this->language->get('repeated_slug');
        }

        if (empty($data['slug'])) {
            $errors['slug'] = $this->language->get('invalid_value');
        }

        if (!\Aurora\App\Permission::can('edit_tags')) {
            http_response_code(403);
            $errors[0] = $this->language->get('no_permission');
        }

        return $errors;
    }

    public function getCondition(array $filters): string
    {
        $where = [];

        if (!empty($filters['search'])) {
            $search = $this->db->escape($filters['search']);
            $where[] = "(tags.name LIKE '%$search%' OR tags.slug LIKE '%$search%')";
        }

        return implode(' AND ', $where);
    }

    private function getBaseData(array $data): array
    {
        return [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
        ];
    }
}
