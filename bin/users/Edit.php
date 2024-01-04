<?php

namespace Aurora\Bin\Users;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Edit extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('users:edit')
            ->setDescription('Edit an user')
            ->addArgument('id', InputArgument::REQUIRED, 'The user id or email.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $user_mod = new \Aurora\App\Modules\User($this->config['db']);
        $id = $input->getArgument('id');

        $user = $user_mod->get(\Aurora\System\Helper::isValidId($id)
            ? [ 'id' => $id ]
            : [ 'email' => $id ]);

        $roles = $this->config['db']->query('SELECT * FROM roles ORDER BY level ASC')->fetchAll(\PDO::FETCH_KEY_PAIR);

        $name = $io->ask('Name', $user['name'], function($val) {
            if (empty($val)) {
                throw new \RuntimeException('You must type a valid name.');
            }

            return $val;
        });

        $slug = $io->ask('Slug', $user['slug'], function($val) use ($user_mod, $user) {
            if (empty($val)) {
                throw new \RuntimeException('You must type a valid slug.');
            }

            if (!empty($user_mod->get([ 'slug' => $val, '!id' => $user['id'] ]))) {
                throw new \RuntimeException('Another user with that slug already exists.');
            }

            return $val;
        });

        $email = $io->ask('Email', $user['email'], function($val) use ($user_mod, $user) {
            if (empty($val) || filter_var($val, FILTER_VALIDATE_EMAIL) === false) {
                throw new \RuntimeException('You must type a valid email.');
            }

            if (!empty($user_mod->get([ 'email' => $val, '!id' => $user['id'] ]))) {
                throw new \RuntimeException('Another user with that email already exists.');
            }

            return $val;
        });

        $image = $io->ask('Profile image (relative to content directory)', $user['image']);

        $role = $io->choice('Role', $roles, array_search($user['role_slug'], $roles));

        $status = $io->confirm('Active', $user['status']);

        $password = $io->askHidden('Password', function($val) {
            if (!empty($val) && strlen((string) $val) < 8) {
                throw new \RuntimeException('You must type a password at least 8 characters long.');
            }

            return $val;
        });

        if (!empty($password)) {
            $io->askHidden('Password confirm', function($val) use ($password) {
                if ($val !== $password) {
                    throw new \RuntimeException('Password and confirmation do not match.');
                }
            });
        }

        $res = $user_mod->save($user['id'], [
            'name' => $name,
            'slug' => $slug,
            'email' => $email,
            'password' => $password,
            'image' => $image,
            'status' => $status,
            'role' => array_search($role, $roles),
        ]);

        if (!$res) {
            $io->error('An unexpected error has occurred. The user was not edited.');
            return Command::FAILURE;
        }

        $io->success("User $email succesfully edited.");
        return Command::SUCCESS;
    }
}
