<?php

namespace Aurora\Bin\Posts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Listing extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('posts:list')
            ->setDescription('List the posts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([ 'ID', 'Title', 'Slug', 'Description', 'Author', 'Published at', 'Published', 'Image alt', 'Meta title', 'Meta description', 'Canonical URL' ]);

        foreach ((new \Aurora\App\Modules\Post($this->config['db']))->getPage(null, null, '', 'id') as $post) {
            $table->addRow([
                $post['id'],
                $post['title'],
                $post['slug'],
                $post['description'],
                $post['user_name'],
                date('Y-m-d', $post['published_at']),
                $post['status'] ? 'Yes' : 'No',
                $post['image_alt'],
                $post['meta_title'],
                $post['meta_description'],
                $post['canonical_url'],
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
