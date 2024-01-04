<?php

namespace Aurora\Bin\Posts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Create extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('posts:create')
            ->setDescription('Create a new post');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $post_mod = new \Aurora\App\Modules\Post($this->config['db']);
        $users = $this->config['db']->query('SELECT id, slug FROM users ORDER BY name ASC')->fetchAll(\PDO::FETCH_KEY_PAIR);

        $res = $post_mod->add([
            'title' => $title = $io->ask('Title', null, function($val) {
                if (empty($val)) {
                    throw new \RuntimeException('You must type a valid title.');
                }

                return $val;
            }),
            'slug' => $io->ask('Slug', null, function($val) use ($post_mod) {
                if (empty($val)) {
                    throw new \RuntimeException('You must type a valid slug.');
                }

                if (!empty($post_mod->get([ 'slug' => $val ]))) {
                    throw new \RuntimeException('A post with that slug already exists.');
                }

                return $val;
            }),
            'description' => $io->ask('Description'),
            'user_id' => array_search($io->choice('Author', $users), $users),
            'published_at' => $io->ask('Publish date (YYYY-MM-DD)', date('Y-m-d')),
            'status' => $io->confirm('Published'),
            'image' => $io->ask('Image (relative to content directory)'),
            'image_alt' => $io->ask('Image alt'),
            'meta_title' => $io->ask('Meta title'),
            'meta_description' => $io->ask('Meta description'),
            'canonical_url' => $io->ask('Canonical URL'),
            'html' => '',
            'tags' => [],
        ]);

        if (!$res) {
            $io->error('An unexpected error has occurred. The post was not created.');
            return Command::FAILURE;
        }

        $io->success("Post $title succesfully created.");
        return Command::SUCCESS;
    }
}
