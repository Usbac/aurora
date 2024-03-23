<?php

namespace Aurora\App\Modules;

final class Page extends \Aurora\App\ModuleBase
{
    protected string $select = 'pages.*,
        COUNT(DISTINCT views.ip) AS views';
    protected string $table = 'pages';
    protected string $join = 'LEFT JOIN views ON views.type = "page" AND views.item_id = pages.id';
    protected string $group_by = 'pages.id';
    protected array $orders = [
        'title' => 'pages.title ASC, pages.id DESC',
        'status' => 'pages.status DESC, pages.title ASC',
        'edited' => 'pages.edited_at DESC, pages.title ASC',
        'views' => 'views DESC, pages.title ASC',
    ];

    /**
     * Adds a new page
     * @param array $data the page data
     * @return string|bool the id of the new page on success, false otherwise
     */
    public function add(array $data): string|bool
    {
        return $this->db->insert($this->table, $this->getBaseData($data));
    }

    /**
     * Updates an existing page
     * @param int $id the page id
     * @param array $data the new data
     * @return string|bool the id of the page on success, false otherwise
     */
    public function save(int $id, array $data): bool
    {
        return $this->db->update($this->table, $this->getBaseData($data), $id) ? $id : false;
    }

    /**
     * Returns an array with all the page fields that contain an error
     * @param array $data the page fields
     * @param mixed $id the page id
     * @return array the array with the page fields that contain an error
     */
    public function checkFields(array $data, $id): array
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors['title'] = $this->language->get('invalid_value');
        }

        if (isset($data['slug']) && !empty($this->get([ 'slug' => $data['slug'], '!id' => $id ]))) {
            $errors['slug'] = $this->language->get('repeated_slug');
        }

        if (!empty($data['slug']) && !\Aurora\System\Helper::isSlugValid($data['slug'])) {
            $errors['slug'] = $this->language->get('invalid_slug');
        }

        if (!\Aurora\App\Permission::can('edit_pages')) {
            http_response_code(403);
            $errors[0] = $this->language->get('no_permission');
        }

        return $errors;
    }

    /**
     * Returns the query conditions to obtain pages based on the given filters
     * @param array $filters the filters
     * @return string the query conditions
     */
    public function getCondition(array $filters): string
    {
        $where = [];

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = 'pages.status = ' . ((int) $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $this->db->escape($filters['search']);
            $where[] = "pages.title LIKE '%$search%'";
        }

        return implode(' AND ', $where);
    }

    /**
     * Returns the right data to add or save a page
     * @param array $data the page data
     * @return array the right data
     */
    private function getBaseData(array $data): array
    {
        return [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'html' => $data['html'],
            'status' => $data['status'],
            'static' => $data['static'],
            'static_file' => $data['static_file'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'canonical_url' => $data['canonical_url'],
            'edited_at' => time(),
        ];
    }
}
