<?php

namespace Aurora\Bin\Settings;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Listing extends \Aurora\Bin\BaseCommand
{
    protected function configure()
    {
        $this->setName('settings:list')
            ->setDescription('List the system settings');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settings = $this->config['db']->query('SELECT `key`, value FROM settings')->fetchAll(\PDO::FETCH_KEY_PAIR);

        (new Table($output))->setHeaders([ 'Setting', 'Value' ])
            ->setRows([
                [ 'Blog url', $settings['blog_url'] ],
                [ 'Date format', $settings['date_format'] ],
                [ 'Description', $settings['description'] ],
                [ 'Error log filename', $settings['log_file'] ],
                [ 'Footer code', $settings['footer_code'] ],
                [ 'Header code', $settings['header_code'] ],
                [ 'Items per page', $settings['per_page'] ],
                [ 'Language', $settings['language'] ],
                [ 'Logo', $settings['logo'] ],
                [ 'Log errors', $settings['log_errors'] ? 'yes' : 'no' ],
                [ 'Maintenance', $settings['maintenance'] ? 'yes' : 'no' ],
                [ 'Meta description', $settings['meta_description'] ],
                [ 'Meta keywords', $settings['meta_keywords'] ],
                [ 'Meta title', $settings['meta_title'] ],
                [ 'Post code', $settings['post_code'] ],
                [ 'RSS feed URL', $settings['rss'] ],
                [ 'Theme', $settings['theme'] ],
                [ 'Timezone', $settings['timezone'] ],
                [ 'Title', $settings['title'] ],
                [ 'Views count', $settings['views_count'] ? 'yes' : 'no' ],
            ])->render();

        return Command::SUCCESS;
    }
}
