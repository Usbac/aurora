<?php

namespace Aurora\Bin\Users;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Listing extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('users:list')
            ->setDescription('List the users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([ 'ID', 'Name', 'Slug', 'Email', 'Active', 'Role', 'Posts', 'Last active', 'Created at' ]);

        foreach ((new \Aurora\App\Modules\User($this->config['db']))->getPage(null, null, '', 'id') as $user) {
            $table->addRow([
                $user['id'],
                $user['name'],
                $user['slug'],
                $user['email'],
                $user['status'] ? 'Yes' : 'No',
                $user['role_slug'],
                $user['posts'],
                date('Y-m-d', $user['last_active']),
                date('Y-m-d', $user['created_at'])
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
