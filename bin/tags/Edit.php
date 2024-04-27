<?php

namespace Aurora\Bin\Tags;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Edit extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('tags:edit')
            ->setDescription('Edit a tag')
            ->addArgument('id', InputArgument::REQUIRED, 'The tag id or slug.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $tag_mod = new \Aurora\App\Modules\Tag($this->config['db']);
        $id = $input->getArgument('id');

        $tag = $tag_mod->get(\Aurora\System\Helper::isValidId($id)
            ? [ 'id' => $id ]
            : [ 'slug' => $id ]);

        if (empty($tag)) {
            $io->error('No tag with the given id or slug exists.');
            return Command::INVALID;
        }

        $res = $tag_mod->save($id, [
            'name' => $name = $io->ask('Name', $tag['name'], function($val) {
                if (empty($val)) {
                    throw new \RuntimeException('You must type a valid name.');
                }

                return $val;
            }),
            'slug' => $io->ask('Slug', $tag['slug'], function($val) use ($tag_mod, $tag) {
                if (empty($val) || !\Aurora\System\Helper::isSlugValid($val)) {
                    throw new \RuntimeException('You must type a valid slug.');
                }

                if (!empty($tag_mod->get([ 'slug' => $val, '!id' => $tag['id'] ]))) {
                    throw new \RuntimeException('Another tag with that slug already exists.');
                }

                return $val;
            }),
            'description' => $io->ask('Description', $tag['description']),
            'meta_title' => $io->ask('Meta title', $tag['meta_title']),
            'meta_description' => $io->ask('Meta description', $tag['meta_description']),
        ]);

        if (!$res) {
            $io->error('An unexpected error has occurred. The tag was not edited.');
            return Command::FAILURE;
        }

        $io->success("Tag $name succesfully edited.");
        return Command::SUCCESS;
    }
}
