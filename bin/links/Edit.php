<?php

namespace Aurora\Bin\Links;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Edit extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('links:edit')
            ->setDescription('Edit a link')
            ->addArgument('id', InputArgument::REQUIRED, 'The link id or slug.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $link_mod = new \Aurora\App\Modules\Link($this->config['db']);
        $id = $input->getArgument('id');

        $link = $link_mod->get(\Aurora\Core\Helper::isValidId($id)
            ? [ 'id' => $id ]
            : [ 'slug' => $id ]);

        $res = $link_mod->save($link['id'], [
            'title' => $title = $io->ask('Title', $link['title'], function($val) {
                if (empty($val)) {
                    throw new \RuntimeException('You must type a valid title.');
                }

                return $val;
            }),
            'url' => $io->ask('URL', $link['url']),
            'order' => $io->ask('Order', $link['order']),
            'status' => $io->confirm('Active', $link['status']),
        ]);

        if (!$res) {
            $io->error('An unexpected error has occurred. The link was not edited.');
            return Command::FAILURE;
        }

        $io->success("Link $title succesfully edited.");
        return Command::SUCCESS;
    }
}
