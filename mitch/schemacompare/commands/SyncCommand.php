<?php

namespace mitch\schemacompare\commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use \mitch\schemacompare\providers\MysqlSchemaProvider;
use \mitch\schemacompare\SchemaDump;

class SyncCommand extends DbCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('sync');
        $this->addOption('generator', 'g', InputOption::VALUE_OPTIONAL, 'which generator use: yii1,yii2,raw', 'raw');
        $this->addOption('dpath', null, InputOption::VALUE_OPTIONAL, 'where to save generated files', '');
        $this->addOption('execute', 'e', InputOption::VALUE_OPTIONAL, 'execute if generator is raw', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $output->writeln("Get schema from db");
        $provider = new \mitch\schemacompare\providers\MysqlSchemaProvider(['database' => $this->db]);
        $schema1 = $provider->getSchema();

        $output->writeln("Get schema yml db");
        $schema2 = new \mitch\schemacompare\providers\YamlSchemaProvider(['path' => $this->path]);
        $schema2 = $schema2->getSchema();

        $g = $input->getOption('generator');
        $dpath = $input->getOption('dpath');

        $generator = null;

        if ($g === 'raw') {
            $generator = new \mitch\schemacompare\MysqlSchemaGenerator([
                'database' => $this->db
            ]);
        }
        if ($g === 'yii1') {
            $generator = new \mitch\schemacompare\Yii1SchemaGenerator([
                'database' => $this->db,
                'path' => $dpath
            ]);
        }

        if ($g === 'yii2') {
            $generator = new \mitch\schemacompare\Yii2SchemaGenerator([
                'database' => $this->db,
                'path' => $dpath
            ]);
        }

        $compare = new \mitch\schemacompare\SchemaCompare([
            'schema1' => $schema1,
            'schema2' => $schema2,
            'generator' => $generator,
            'database' => $this->db,
        ]);

        $output->writeln("compare");
        $generator = $compare->compare();

        $output->writeln("migrate");
        $generator->migrate($input->getOption('execute'));

        $output->writeln("done");
    }
}