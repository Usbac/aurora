<?php

namespace Aurora\Bin\Settings;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

class Set extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('settings:set')
            ->setDescription('Set the value of a system setting')
            ->addArgument('name', InputArgument::REQUIRED, 'Setting name.')
            ->addArgument('value', InputArgument::REQUIRED, 'Setting value.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $names = [
            'Blog url' => 'blog_url',
            'Date format' => 'date_format',
            'Description' => 'description',
            'Editor code' => 'editor_code',
            'Error log filename' => 'log_file',
            'Footer code' => 'footer_code',
            'Header code' => 'header_code',
            'Items per page' => 'per_page',
            'Language' => 'language',
            'Logo' => 'logo',
            'Log errors' => 'log_errors',
            'Maintenance' => 'maintenance',
            'Meta description' => 'meta_description',
            'Meta keywords' => 'meta_keywords',
            'Meta title' => 'meta_title',
            'Post code' => 'post_code',
            'RSS feed URL' => 'rss',
            'SameSite cookie' => 'samesite_cookie',
            'Session lifetime' => 'session_lifetime',
            'Theme' => 'theme',
            'Timezone' => 'timezone',
            'Title' => 'title',
            'Views count' => 'views_count',
        ];

        $field = $names[$input->getArgument('name')] ?? null;

        if (!isset($field)) {
            $io->error("Invalid setting name given, must be one of these: \n" . implode("\n", array_keys($names)));
            return Command::INVALID;
        }

        return $this->config['db']->query('UPDATE settings SET value = ? WHERE `key` = ?', $input->getArgument('value'), $field)
            ? Command::SUCCESS
            : Command::FAILURE;
    }
}
