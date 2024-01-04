<?php

namespace Aurora\Bin\Users;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Create extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('users:create')
            ->setDescription('Create a new user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $user_mod = new \Aurora\App\Modules\User($this->config['db']);
        $roles = $this->config['db']->query('SELECT * FROM roles ORDER BY level ASC')->fetchAll(\PDO::FETCH_KEY_PAIR);

        $name = $io->ask('Name', null, function($val) {
            if (empty($val)) {
                throw new \RuntimeException('You must type a valid name.');
            }

            return $val;
        });

        $slug = $io->ask('Slug', null, function($val) use ($user_mod) {
            if (empty($val)) {
                throw new \RuntimeException('You must type a valid slug.');
            }

            if (!empty($user_mod->get([ 'slug' => $val ]))) {
                throw new \RuntimeException('An user with that slug already exists.');
            }

            return $val;
        });

        $email = $io->ask('Email', null, function($val) use ($user_mod) {
            if (empty($val) || filter_var($val, FILTER_VALIDATE_EMAIL) === false) {
                throw new \RuntimeException('You must type a valid email.');
            }

            if (!empty($user_mod->get([ 'email' => $val ]))) {
                throw new \RuntimeException('An user with that email already exists.');
            }

            return $val;
        });

        $image = $io->ask('Profile image (relative to content directory)');

        $role = $io->choice('Role', $roles, $roles[array_key_first($roles)]);

        $status = $io->confirm('Active');

        $password = $io->askHidden('Password', function($val) {
            if (strlen((string) $val) < 8) {
                throw new \RuntimeException('You must type a password at least 8 characters long.');
            }

            return $val;
        });

        $io->askHidden('Password confirm', function($val) use ($password) {
            if ($val !== $password) {
                throw new \RuntimeException('Password and confirmation do not match.');
            }
        });

        $res = $user_mod->add([
            'name' => $name,
            'slug' => $slug,
            'email' => $email,
            'password' => $password,
            'image' => $image,
            'status' => $status,
            'role' => array_search($role, $roles),
        ]);

        if (!$res) {
            $io->error('An unexpected error has occurred. The user was not created.');
            return Command::FAILURE;
        }

        $io->success("User $email succesfully created.");
        return Command::SUCCESS;
    }
}
