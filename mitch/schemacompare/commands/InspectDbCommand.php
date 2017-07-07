<?php

namespace mitch\schemacompare\commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use \mitch\schemacompare\providers\MysqlSchemaProvider;
use \mitch\schemacompare\SchemaDump;

/**
 * Create yml from db
 * Class InspectDbCommand
 * @package mitch\schemacompare\commands
 */
class InspectDbCommand extends DbCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('inspectdb');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $provider = new MysqlSchemaProvider(['database' => $this->db]);

        $output->writeln("Write schema to file");
        $schema = $provider->getSchema();

        $output->writeln("write schema to file: " . $this->path);
        SchemaDump::Dump($schema, $this->path);

        $output->writeln("done");
    }
}