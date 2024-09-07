<?php

namespace Aurora\Bin\Tags;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Delete extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('tags:delete')
            ->setDescription('Delete a tag')
            ->addArgument('id', InputArgument::REQUIRED, 'The tag id or slug.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $tag_mod = new \Aurora\App\Modules\Tag($this->config['db']);
        $id = $input->getArgument('id');

        $tag = $tag_mod->get(\Aurora\Core\Helper::isValidId($id)
            ? [ 'id' => $id ]
            : [ 'slug' => $id ]);

        if (empty($tag)) {
            $io->error('No tag with the given id or slug exists.');
            return Command::INVALID;
        }

        if ($io->confirm('Are you sure about deleting tag ' . $tag['name'])) {
            if (!$tag_mod->remove($tag['id'])) {
                $io->error('An unexpected error has occurred. The tag was not deleted.');
                return Command::FAILURE;
            }

            $io->success('Tag ' . $tag['name'] . ' succesfully deleted.');
        }

        return Command::SUCCESS;
    }
}
