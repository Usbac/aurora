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

    /**
     * Returns the links that must be shown in the header
     * @return array the links
     */
    public function getHeaderLinks(): array
    {
        return $this->getPage(null, null, 'status', 'order');
    }

    /**
     * Adds a new link
     * @param array $data the link data
     * @return string|bool the id of the new link on success, false otherwise
     */
    public function add(array $data): string|bool
    {
        return $this->db->insert($this->table, $this->getBaseData($data));
    }

    /**
     * Updates an existing link
     * @param int $id the link id
     * @param array $data the new data
     * @return string|bool the id of the link on success, false otherwise
     */
    public function save(int $id, array $data): bool
    {
        return $this->db->update($this->table, $this->getBaseData($data), $id) ? $id : false;
    }

    /**
     * Returns an array with all the link fields that contain an error
     * @param array $data the link fields
     * @return array the array with the link fields that contain an error
     */
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

    /**
     * Returns the query conditions to obtain links based on the given filters
     * @param array $filters the filters
     * @return string the query conditions
     */
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

    /**
     * Returns the right data to add or save a link
     * @param array $data the link data
     * @return array the right data
     */
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
