<?php

namespace Aurora\App\Modules;

final class User extends \Aurora\App\ModuleBase
{
    protected string $select = 'users.*, COUNT(posts.id) AS posts, roles.slug AS role_slug';
    protected string $table = 'users';
    protected string $join = 'LEFT JOIN posts ON posts.user_id = users.id
        LEFT JOIN roles ON roles.level = users.role';
    protected string $group_by = 'users.id';
    protected array $relations = [ 'password_restores' => 'user_id' ];
    protected array $orders = [
        'name' => 'users.name ASC, users.id DESC',
        'email' => 'users.email ASC, users.name ASC',
        'posts' => 'COUNT(posts.id) DESC, users.name ASC',
        'status' => 'users.status DESC, users.name ASC',
        'role' => 'users.role DESC, users.name ASC',
        'last_active' => 'users.last_active DESC, users.name ASC',
        'id' => 'users.id ASC',
    ];

    public function save(int $id, array $data): int|false
    {
        $res = $this->db->update($this->table, [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'email' => $data['email'],
            'status' => $data['status'],
            'image' => $data['image'],
            'role' => $data['role'],
        ], $id);

        if ($res && !empty($data['password'])) {
            $this->db->update($this->table, [ 'password' => $this->getPassword($data['password']) ], $id);
        }

        return $res ? $id : false;
    }

    public function add(array $data): string|false
    {
        $time = time();
        return $this->db->insert($this->table, [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'email' => $data['email'],
            'password' => $this->getPassword($data['password']),
            'status' => $data['status'],
            'image' => $data['image'] ?? null,
            'role' => $data['role'],
            'created_at' => $time,
            'last_active' => $time,
        ]);
    }

    public function handleLogin(string $email, string $password): array
    {
        $user = $this->get([
            'email' => $email,
            'status' => 1,
        ]);
        $errors = [];

        if (!$user) {
            $errors['email'] = $this->language->get('no_active_user');
        } elseif (!password_verify($password, $user['password'])) {
            $errors['password'] = $this->language->get('wrong_password');
        }

        if (empty($errors)) {
            $_SESSION['user'] = $user;
        }

        return $errors;
    }

    public function requestPasswordRestore(string $email, string $hash, string $message): array
    {
        $user = $this->get([
            'email' => $email,
            'status' => 1,
        ]);
        $errors = [];

        if (!$user) {
            $errors['email'] = $this->language->get('no_active_user');
        }

        if (empty($errors)) {
            $this->db->replace('password_restores', [
                'user_id' => $user['id'],
                'hash' => $hash,
                'created_at' => time(),
            ]);

            if (!\Aurora\System\Kernel::config('mail')($email, $this->language->get('restore_your_password'), $message)) {
                $errors['email'] = $this->language->get('error_sending_email');
            }
        }

        return $errors;
    }

    public function passwordRestore(string $hash, string $password, string $password_confirm): string
    {
        $restore = $this->db->query('SELECT * FROM password_restores WHERE hash = ?', $hash)->fetch();

        if (empty($restore) || $restore['created_at'] < strtotime('-2 hours')) {
            return $this->language->get('error_expired_restore');
        }

        $error = $this->checkPassword($password, $password_confirm);
        if (empty($error)) {
            $user = $this->get([
                'id' => $restore['user_id'],
                'status' => 1,
            ]);

            if (!$user) {
                return $this->language->get('no_active_user');
            }

            $this->db->delete('password_restores', $hash, 'hash');
            $this->db->update($this->table, [ 'password' => $this->getPassword($password) ], $user['id']);
            $_SESSION['user'] = $user;
        }

        return $error;
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

        if (!empty($this->get([ 'email' => $data['email'], '!id' => $id ]))) {
            $errors['email'] = $this->language->get('repeated_email');
        }

        if (empty($data['email']) ||
            filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = $this->language->get('invalid_value');
        }

        if (empty($id) && empty($data['password'])) {
            $errors['password'] = $this->language->get('bad_password');
        }

        if (!empty($data['password'])) {
            $password_error = $this->checkPassword($data['password'], $data['password_confirm']);
            if (!empty($password_error)) {
                $errors['password'] = $password_error;
            }
        }

        if (!\Aurora\App\Permission::can('edit_users')) {
            http_response_code(403);
            $errors[0] = $this->language->get('no_permission');
        }

        return $errors;
    }

    public function getCondition(array $filters): string
    {
        $where = [];

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = 'users.status = ' . ((int) $filters['status']);
        }

        if (isset($filters['role']) && $filters['role'] !== '') {
            $where[] = 'users.role = ' . ((int) $filters['role']);
        }

        if (!empty($filters['search'])) {
            $search = $this->db->escape($filters['search']);
            $where[] = "(users.name LIKE '%$search%' OR users.email LIKE '%$search%')";
        }

        return implode(' AND ', $where);
    }

    public function getPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, [ 'cost' => 10 ]);
    }

    private function checkPassword(string $password, string $password_confirm): string
    {
        if (strlen($password) < 8) {
            return $this->language->get('bad_password');
        }

        if ($password !== $password_confirm) {
            return $this->language->get('bad_password_confirm');
        }

        return '';
    }
}
