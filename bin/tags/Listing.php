<?php

namespace Aurora\Bin\Tags;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Listing extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('tags:list')
            ->setDescription('List the tags');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([ 'ID', 'Name', 'Slug', 'Description', 'Posts', 'Meta title', 'Meta description' ]);

        foreach ((new \Aurora\App\Modules\Tag($this->config['db']))->getPage(null, null, '', 'id') as $tag) {
            $table->addRow([
                $tag['id'],
                $tag['name'],
                $tag['slug'],
                $tag['description'],
                $tag['posts'],
                $tag['meta_title'],
                $tag['meta_description'],
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
