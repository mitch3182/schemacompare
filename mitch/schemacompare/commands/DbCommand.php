<?php

namespace mitch\schemacompare\commands;

use mitch\schemacompare\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbCommand extends Command
{
    /** @var Database */
    public $db;
    /** @var string */
    public $path;

    protected function configure()
    {
        $this->setName('unknown')
            ->setDescription('get schema from db and write it to yml file')
            ->addArgument('path', InputArgument::REQUIRED, 'path to save yml')
            ->addOption('host', 'l', InputArgument::OPTIONAL, 'database host', 'localhost')
            ->addOption('user', 'u', InputArgument::OPTIONAL, 'database user', 'root')
            ->addOption('password', 'w', InputArgument::OPTIONAL, 'database password', '')
            ->addOption('port', 'p', InputArgument::OPTIONAL, 'database port', '')
            ->addArgument('database',InputArgument::REQUIRED, 'database name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->db = new \mitch\schemacompare\Database([
            'database' => $input->getArgument('database'),
            'user' => $input->getOption('user'),
            'password' => $input->getOption('password'),
            'port' => (int)$input->getOption('port'),
            'host' => $input->getOption('host'),
        ]);

        $this->path = $input->getArgument('path');
    }
}