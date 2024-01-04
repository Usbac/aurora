<?php

namespace Aurora\Bin\Links;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Listing extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('links:list')
            ->setDescription('List the links');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([ 'ID', 'Title', 'URL', 'Order', 'Active' ]);

        foreach ((new \Aurora\App\Modules\Link($this->config['db']))->getPage(null, null, '', 'id') as $link) {
            $table->addRow([
                $link['id'],
                $link['title'],
                $link['url'],
                $link['order'],
                $link['status'] ? 'Yes' : 'No',
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
