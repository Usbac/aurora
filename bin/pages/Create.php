<?php

namespace Aurora\Bin\Pages;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Create extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('pages:create')
            ->setDescription('Create a new page');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $page_mod = new \Aurora\App\Modules\Page($this->config['db']);

        $res = $page_mod->add([
            'title' => $title = $io->ask('Title', null, function($val) {
                if (empty($val)) {
                    throw new \RuntimeException('You must type a valid title.');
                }

                return $val;
            }),
            'slug' => $io->ask('Slug', null, function($val) use ($page_mod) {
                if (!empty($val) && !\Aurora\System\Helper::isSlugValid($val)) {
                    throw new \RuntimeException('You must type a valid slug.');
                }

                if (!empty($page_mod->get([ 'slug' => $val ]))) {
                    throw new \RuntimeException('A page with that slug already exists.');
                }

                return $val;
            }),
            'status' => $io->confirm('Published'),
            'static' => $io->confirm('Static page'),
            'static_file' => $io->choice('Static page file', $this->getThemeFiles(), ''),
            'meta_title' => $io->ask('Meta title'),
            'meta_description' => $io->ask('Meta description'),
            'canonical_url' => $io->ask('Canonical URL'),
            'html' => '',
        ]);

        if (!$res) {
            $io->error('An unexpected error has occurred. The page was not created.');
            return Command::FAILURE;
        }

        $io->success("Page $title succesfully created.");
        return Command::SUCCESS;
    }
}
