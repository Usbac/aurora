<?php

namespace Aurora\App\Modules;

final class Link extends \Aurora\App\ModuleBase
{
    protected string $table = 'links';
    protected array $orders = [
        'title' => 'links.title ASC, links.id DESC',
        'url' => 'links.url ASC, links.title ASC',
        'order' => 'links.`order` ASC, links.title ASC',
        'status' => 'links.status DESC, links.title ASC',
    ];

    public function getHeaderLinks(): array
    {
        return $this->getPage(null, null, 'status', 'order');
    }

    public function add(array $data): string|false
    {
        return $this->db->insert($this->table, $this->getBaseData($data));
    }

    public function save(int $id, array $data): bool
    {
        return $this->db->update($this->table, $this->getBaseData($data), $id) ? $id : false;
    }

    public function checkFields(array $data): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = $this->language->get('invalid_value');
        }

        if (!\Aurora\App\Permission::can('edit_links')) {
            http_response_code(403);
            $errors[0] = $this->language->get('no_permission');
        }

        return $errors;
    }

    public function getCondition(array $filters): string
    {
        $where = [];

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = 'links.status = ' . ((int) $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $this->db->escape($filters['search']);
            $where[] = "(links.title LIKE '%$search%' OR links.url LIKE '%$search%')";
        }

        return implode(' AND ', $where);
    }

    private function getBaseData(array $data): array
    {
        return [
            'title' => $data['title'],
            'url' => $data['url'],
            'order' => $data['order'],
            'status' => $data['status'],
        ];
    }
}
