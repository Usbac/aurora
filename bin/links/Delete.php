<?php

namespace Aurora\Bin\Links;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Delete extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('links:delete')
            ->setDescription('Delete a link')
            ->addArgument('id', InputArgument::REQUIRED, 'The link id or slug.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $link_mod = new \Aurora\App\Modules\Link($this->config['db']);
        $id = $input->getArgument('id');

        $link = $link_mod->get(\Aurora\System\Helper::isValidId($id)
            ? [ 'id' => $id ]
            : [ 'slug' => $id ]);

        if (empty($link)) {
            $io->error('No link with the given id or slug exists.');
            return Command::INVALID;
        }

        if ($io->confirm('Are you sure about deleting link ' . $link['title'])) {
            if (!$link_mod->remove($link['id'])) {
                $io->error('An unexpected error has occurred. The link was not deleted.');
                return Command::FAILURE;
            }

            $io->success('Link ' . $link['title'] . ' succesfully deleted.');
        }

        return Command::SUCCESS;
    }
}
