<?php

namespace Aurora\Bin\Pages;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Delete extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('pages:delete')
            ->setDescription('Delete a page')
            ->addArgument('id', InputArgument::REQUIRED, 'The page id or slug.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $page_mod = new \Aurora\App\Modules\Page($this->config['db']);
        $id = $input->getArgument('id');

        $page = $page_mod->get(\Aurora\Core\Helper::isValidId($id)
            ? [ 'id' => $id ]
            : [ 'slug' => $id ]);

        if (empty($page)) {
            $io->error('No page with the given id or slug exists.');
            return Command::INVALID;
        }

        if ($io->confirm('Are you sure about deleting page ' . $page['title'])) {
            if (!$page_mod->remove($page['id'])) {
                $io->error('An unexpected error has occurred. The page was not deleted.');
                return Command::FAILURE;
            }

            $io->success('Page ' . $page['title'] . ' succesfully deleted.');
        }

        return Command::SUCCESS;
    }
}
