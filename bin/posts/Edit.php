<?php

namespace Aurora\Bin\Posts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Edit extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('posts:edit')
            ->setDescription('Edit a post')
            ->addArgument('id', InputArgument::REQUIRED, 'The post id or slug.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $users = $this->config['db']->query('SELECT id, slug FROM users ORDER BY name ASC')->fetchAll(\PDO::FETCH_KEY_PAIR);
        $post_mod = new \Aurora\App\Modules\Post($this->config['db']);
        $id = $input->getArgument('id');

        $post = $post_mod->get(\Aurora\System\Helper::isValidId($id)
            ? [ 'id' => $id ]
            : [ 'slug' => $id ]);

        if (empty($post)) {
            $io->error('No post with the given id or slug exists.');
            return Command::INVALID;
        }

        $res = $post_mod->save($post['id'], [
            'title' => $title = $io->ask('Title', $post['title'], function($val) {
                if (empty($val)) {
                    throw new \RuntimeException('You must type a valid title.');
                }

                return $val;
            }),
            'slug' => $io->ask('Slug', $post['slug'], function($val) use ($post_mod, $post) {
                if (empty($val) || !\Aurora\System\Helper::isSlugValid($val)) {
                    throw new \RuntimeException('You must type a valid slug.');
                }

                if (!empty($post_mod->get([ 'slug' => $val, '!id' => $post['id'] ]))) {
                    throw new \RuntimeException('Another post with that slug already exists.');
                }

                return $val;
            }),
            'description' => $io->ask('Description', $post['description']),
            'user_id' => array_search($io->choice('Author', $users, $post['user_id']), $users),
            'published_at' => $io->ask('Publish date (YYYY-MM-DD)', date('Y-m-d', $post['published_at'])),
            'status' => $io->confirm('Published', $post['status']),
            'image' => $io->ask('Image (relative to content directory)', $post['image']),
            'image_alt' => $io->ask('Image alt', $post['image_alt']),
            'meta_title' => $io->ask('Meta title', $post['meta_title']),
            'meta_description' => $io->ask('Meta description', $post['meta_description']),
            'canonical_url' => $io->ask('Canonical URL', $post['canonical_url']),
            'html' => $post['html'],
            'tags' => explode(',', $post['tags_id']),
        ]);

        if (!$res) {
            $io->error('An unexpected error has occurred. The post was not edited.');
            return Command::FAILURE;
        }

        $io->success("Post $title succesfully edited.");
        return Command::SUCCESS;
    }
}
