<?php

namespace Aurora\Bin;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Update extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('update')
            ->setDescription('Update Aurora to the latest compatible version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $update = new \Aurora\App\Update();
        $latest_release = $update->getLatestRelease();

        if ($latest_release == \Aurora\App\Update::ERROR_CONNECTION) {
            $io->error('The list of versions could not be fetched.');
            return Command::FAILURE;
        }

        if (empty($latest_release) || \Aurora\System\Kernel::VERSION == $latest_release['version']) {
            $io->success('You already have the latest version (' . \Aurora\System\Kernel::VERSION . ') compatible with your Aurora installation.');
            return Command::SUCCESS;
        }

        if (!$io->confirm("Are you sure about updating Aurora to version " . $latest_release['version'] . "?\n It is recommended to make a backup of your files before continuing.", false)) {
            $output->writeln('The update has ben cancelled.');
            return Command::SUCCESS;
        }

        switch ($update->run($latest_release['zip'])) {
            case \Aurora\App\Update::ERROR_CONNECTION:
                $io->error('The update file could not be downloaded.');
                return Command::FAILURE;
            case \Aurora\App\Update::ERROR_ZIP:
                $io->error('The update file could not be extracted.');
                return Command::FAILURE;
            case \Aurora\App\Update::ERROR_COPY:
                $io->error("An error occurred while copying files. The update could not be completed.\nPlease make sure your files have the right permissions and try again.");
                return Command::FAILURE;
        }

        $io->success('Aurora has been succesfully updated to version ' . $latest_release['version'] . '.');
        return Command::SUCCESS;
    }
}
