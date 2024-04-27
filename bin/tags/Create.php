<?php

namespace Aurora\Bin\Tags;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Create extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('tags:create')
            ->setDescription('Create a new tag');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $tag_mod = new \Aurora\App\Modules\Tag($this->config['db']);

        $res = $tag_mod->add([
            'name' => $name = $io->ask('Name', null, function($val) {
                if (empty($val)) {
                    throw new \RuntimeException('You must type a valid name.');
                }

                return $val;
            }),
            'slug' => $io->ask('Slug', null, function($val) use ($tag_mod) {
                if (empty($val) || !\Aurora\System\Helper::isSlugValid($val)) {
                    throw new \RuntimeException('You must type a valid slug.');
                }

                if (!empty($tag_mod->get([ 'slug' => $val ]))) {
                    throw new \RuntimeException('A tag with that slug already exists.');
                }

                return $val;
            }),
            'description' => $io->ask('Description'),
            'meta_title' => $io->ask('Meta title'),
            'meta_description' => $io->ask('Meta description'),
        ]);

        if (!$res) {
            $io->error('An unexpected error has occurred. The tag was not created.');
            return Command::FAILURE;
        }

        $io->success("Tag $name succesfully created.");
        return Command::SUCCESS;
    }
}
