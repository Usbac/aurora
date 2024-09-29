<?php

namespace Aurora\App\Modules;

final class User extends \Aurora\App\ModuleBase
{
    public const DEFAULT_ORDER = 'name';
    public const DEFAULT_SORT = 'asc';

    protected string $select = 'users.*, COUNT(posts.id) AS posts, roles.slug AS role_slug';
    protected string $table = 'users';
    protected string $join = 'LEFT JOIN posts ON posts.user_id = users.id
        LEFT JOIN roles ON roles.level = users.role';
    protected string $group_by = 'users.id';
    protected array $relations = [ 'password_restores' => 'user_id' ];
    protected array $orders = [
        'name' => 'users.name',
        'email' => 'users.email',
        'posts' => 'COUNT(posts.id)',
        'status' => 'users.status',
        'role' => 'users.role',
        'last_active' => 'users.last_active',
        'id' => 'users.id',
    ];

    /**
     * Updates an existing user
     * @param int $id the user id
     * @param array $data the new data
     * @return string|bool the id of the user on success, false otherwise
     */
    public function save(int $id, array $data): int|false
    {
        $res = $this->db->update($this->table, [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'email' => $data['email'],
            'status' => $data['status'],
            'image' => $data['image'],
            'bio' => $data['bio'],
            'role' => $data['role'],
        ], $id);

        if ($res && !empty($data['password'])) {
            $this->db->update($this->table, [ 'password' => $this->getPassword($data['password']) ], $id);
        }

        return $res ? $id : false;
    }

    /**
     * Adds a new user
     * @param array $data the user data
     * @return string|bool the id of the new user on success, false otherwise
     */
    public function add(array $data): string|bool
    {
        $time = time();
        return $this->db->insert($this->table, [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'email' => $data['email'],
            'password' => $this->getPassword($data['password']),
            'status' => $data['status'],
            'image' => $data['image'] ?? null,
            'bio' => $data['bio'],
            'role' => $data['role'],
            'created_at' => $time,
            'last_active' => $time,
        ]);
    }

    /**
     * Handles the login of an user
     * @param string $email the user's email
     * @param string $password the user's password
     * @return array the array with the login errors, if empty it means the user has successfully logged in.
     */
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

    /**
     * Sends an email to restore the password of an user
     * @param string $email the user's email
     * @param string $hash the hash to restore the password
     * @param string $message the email's content
     * @return array the array with the errors, if empty it means the email has been sent.
     */
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

            if (!\Aurora\Core\Kernel::config('mail')($email, $this->language->get('restore_your_password'), $message)) {
                $errors['email'] = $this->language->get('error_sending_email');
            }
        }

        return $errors;
    }

    /**
     * Restores an user's password
     * @param string $hash the hash to restore the password
     * @param string $password the new password
     * @param string $password_confirm the confirmation of the new password
     * @return string the error message, if empty it means the password has been successfully been restored
     */
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

    /**
     * Returns an array with all the user fields that contain an error
     * @param array $data the user fields
     * @return array the array with the user fields that contain an error
     */
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

        if (empty($data['slug']) || !\Aurora\Core\Helper::isSlugValid($data['slug'])) {
            $errors['slug'] = $this->language->get('invalid_slug');
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

    /**
     * Returns the query conditions to obtain users based on the given filters
     * @param array $filters the filters
     * @return string the query conditions
     */
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

    /**
     * Returns the given password hashed
     * @param string $password the password
     * @return string the password hashed
     */
    public function getPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, [ 'cost' => 10 ]);
    }

    /**
     * Checks the given password and its confirmation
     * @param string $password the password
     * @param string $password_confirm the password confirmation
     * @return string the error message, if empty it means both passwords are equal and valid
     */
    private function checkPassword(string $password, string $password_confirm): string
    {
        if (mb_strlen($password) < 8) {
            return $this->language->get('bad_password');
        }

        if ($password !== $password_confirm) {
            return $this->language->get('bad_password_confirm');
        }

        return '';
    }
}
