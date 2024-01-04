<?php

namespace Aurora\Bin\Users;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Delete extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('users:delete')
            ->setDescription('Delete an user')
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

        if (empty($user)) {
            $io->error('No user with the given id or email exists.');
            return Command::INVALID;
        }

        if ($io->confirm('Are you sure about deleting user ' . $user['email'])) {
            if (!$user_mod->remove($user['id'])) {
                $io->error('An unexpected error has occurred. The user was not deleted.');
                return Command::FAILURE;
            }

            $io->success('User ' . $user['email'] . ' succesfully deleted.');
        }

        return Command::SUCCESS;
    }
}
