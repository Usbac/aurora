<?php

namespace Aurora\Bin\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

class Restore extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('db:restore')
            ->setDescription('Restore the database from a json file')
            ->addArgument('file', InputArgument::REQUIRED, 'The file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $file = getcwd() . '/' . $input->getArgument('file');

        try {
            $json = json_decode(file_get_contents($file), true);
            $success = (new \Aurora\App\Migration($this->config['db']))->import($json['tables'] ?? false);
        } catch (\Throwable $e) {
            $success = false;
        }

        if ($success) {
            $io->success("Database succesfully restored from $file");
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
