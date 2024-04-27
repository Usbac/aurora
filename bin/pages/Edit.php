<?php

namespace Aurora\Bin\Pages;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Edit extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('pages:edit')
            ->setDescription('Edit a page')
            ->addArgument('id', InputArgument::REQUIRED, 'The page id or slug.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $page_mod = new \Aurora\App\Modules\Page($this->config['db']);
        $id = $input->getArgument('id');

        $page = $page_mod->get(\Aurora\System\Helper::isValidId($id)
            ? [ 'id' => $id ]
            : [ 'slug' => $id ]);

        if (empty($page)) {
            $io->error('No page with the given id or slug exists.');
            return Command::INVALID;
        }

        $res = $page_mod->save($page['id'], [
            'title' => $title = $io->ask('Title', $page['title'], function($val) {
                if (empty($val)) {
                    throw new \RuntimeException('You must type a valid title.');
                }

                return $val;
            }),
            'slug' => $io->ask('Slug', $page['slug'], function($val) use ($page_mod, $page) {
                if (!empty($val) && !\Aurora\System\Helper::isSlugValid($val)) {
                    throw new \RuntimeException('You must type a valid slug.');
                }

                if (!empty($page_mod->get([ 'slug' => $val, '!id' => $page['id'] ]))) {
                    throw new \RuntimeException('A page with that slug already exists.');
                }

                return $val;
            }),
            'status' => $io->confirm('Published', $page['status']),
            'static' => $io->confirm('Static page', $page['static']),
            'static_file' => $io->choice('Static page file', $this->getThemeFiles(), $page['static_file']),
            'meta_title' => $io->ask('Meta title', $page['meta_title']),
            'meta_description' => $io->ask('Meta description', $page['meta_description']),
            'canonical_url' => $io->ask('Canonical URL', $page['canonical_url']),
            'html' => $page['html'],
        ]);

        if (!$res) {
            $io->error('An unexpected error has occurred. The page was not edited.');
            return Command::FAILURE;
        }

        $io->success("Page $title succesfully edited.");
        return Command::SUCCESS;
    }
}
