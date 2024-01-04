<?php

namespace Aurora\Bin\Links;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Create extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('links:create')
            ->setDescription('Create a new link');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $link_mod = new \Aurora\App\Modules\Link($this->config['db']);

        $res = $link_mod->add([
            'title' => $title = $io->ask('Title', null, function($val) {
                if (empty($val)) {
                    throw new \RuntimeException('You must type a valid title.');
                }

                return $val;
            }),
            'url' => $io->ask('URL'),
            'order' => $io->ask('Order', '0'),
            'status' => $io->confirm('Active'),
        ]);

        if (!$res) {
            $io->error('An unexpected error has occurred. The link was not created.');
            return Command::FAILURE;
        }

        $io->success("Link $title succesfully created.");
        return Command::SUCCESS;
    }
}
