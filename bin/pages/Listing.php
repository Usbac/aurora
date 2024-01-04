<?php

namespace Aurora\Bin\Pages;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Listing extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('pages:list')
            ->setDescription('List the pages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([ 'ID', 'Title', 'Slug', 'Published', 'Static page', 'Static page file', 'Meta title', 'Meta description', 'Canonical URL' ]);

        foreach ((new \Aurora\App\Modules\Page($this->config['db']))->getPage(null, null, '', 'id') as $page) {
            $table->addRow([
                $page['id'],
                $page['title'],
                $page['slug'],
                $page['status'] ? 'Yes' : 'No',
                $page['static'] ? 'Yes' : 'No',
                $page['static_file'],
                $page['meta_title'],
                $page['meta_description'],
                $page['canonical_url'],
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
