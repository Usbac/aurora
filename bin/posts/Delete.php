<?php

namespace Aurora\Bin\Posts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Delete extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('posts:delete')
            ->setDescription('Delete a post')
            ->addArgument('id', InputArgument::REQUIRED, 'The post id or slug.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $post_mod = new \Aurora\App\Modules\Post($this->config['db']);
        $id = $input->getArgument('id');

        $post = $post_mod->get(\Aurora\Core\Helper::isValidId($id)
            ? [ 'id' => $id ]
            : [ 'slug' => $id ]);

        if (empty($post)) {
            $io->error('No post with the given id or slug exists.');
            return Command::INVALID;
        }

        if ($io->confirm('Are you sure about deleting post ' . $post['title'])) {
            if (!$post_mod->remove($post['id'])) {
                $io->error('An unexpected error has occurred. The post was not deleted.');
                return Command::FAILURE;
            }

            $io->success('Post ' . $post['title'] . ' succesfully deleted.');
        }

        return Command::SUCCESS;
    }
}
