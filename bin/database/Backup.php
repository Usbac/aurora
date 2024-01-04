<?php

namespace Aurora\Bin\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

class Backup extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('db:backup')
            ->setDescription('Create a backup of the database in a json file')
            ->addArgument('file', InputArgument::REQUIRED, 'The file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $file_content = json_encode([
            'meta' => [
                'created' => date('Y-m-d H:i:s'),
                'version' => \Aurora\System\Kernel::VERSION,
            ],
            'tables' => (new \Aurora\App\Migration($this->config['db']))->export(),
        ]);

        $file = getcwd() . '/' . $input->getArgument('file');

        if (file_put_contents($file, $file_content)) {
            $io->success("Backup succesfully created at $file");
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
