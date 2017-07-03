<?php

namespace mitch\schemacompare;

class Yii1SchemaGenerator extends SchemaGenerator
{
    /** @var Database */
    public $database;
    public $path;

    public function renderTemplate($variables)
    {
        $micro_date = microtime();
        $date_array = explode(" ",$micro_date);
        $time = date('ymd_His_') . substr($date_array[0], 2);

        extract($variables);
        $classname = 'm'.$time . '_' . $name;

        $templatePath = __DIR__ . '/../../templates/yii1/Migration.php';
        ob_start();

        require $templatePath;

        $result = ob_get_clean();
        $fileName = $classname .'.php';
        file_put_contents($this->path . '/' . $fileName, $result);
    }

    public function ColumnDefinition(Column $column)
    {
        $notNull = $column->notNull ? 'not null' : '';
        $default = $column->default ? "default '$column->default'" : '';
        $extra = $column->extra;

        return "{$column->dbType} {$notNull} {$default} {$extra}";
    }

    public function DropColumn(Column $one)
    {
        $this->renderTemplate([
            'name' => 'drop_column',
            'code' => "\$this->dropColumn('{$one->table->name}', '{$one->name}');",
        ]);
    }

    public function DropFk(ForeignKey $one)
    {
        $fkName = $this->database->getFkNameFromDb($one);

        $this->renderTemplate([
            'name' => 'drop_fk',
            'code' => "\$this->dropForeignKey('$fkName', '{$one->table->name}');",
        ]);
    }

    public function DropTable(Table $one)
    {
        $this->renderTemplate([
            'name' => 'drop_table',
            'code' => "\$this->dropTable('$fkName');",
        ]);
    }

    public function AlterColumn(Column $one)
    {
        $columnDefinition = $this->ColumnDefinition($one);

        $this->renderTemplate([
            'name' => 'alter_column',
            'code' => "\$this->alterColumn('{$one->table->name}', '{$one->name}', '{$columnDefinition}');",
        ]);
    }

    public function AddColumn(Column $one)
    {
        $columnDefinition = $this->ColumnDefinition($one);

        $this->renderTemplate([
            'name' => 'add_column',
            'code' => "\$this->addColumn('{$one->table->name}', '{$one->name}', '{$columnDefinition}');",
        ]);
    }

    public function CreateTable(Table $one)
    {
        $colDefinitions = '';

        if(!empty($one->pk)){
            $colDefinitions .= "\t\t\t'id' => 'pk',\n";
        }

        foreach($one->columns as $column){

            if ($column->name == 'id') continue;

            $columnDefinition = $this->ColumnDefinition($column);
            $colDefinitions .= "\t\t\t'{$column->name}' => \"$columnDefinition\",\n";
        }

        $this->renderTemplate([
            'name' => 'create_table_' . $one->name,
            'code' => "\$this->createTable('{$one->name}', [\n $colDefinitions \n\t\t]);",
        ]);
    }

    public function CreateFk(ForeignKey $one)
    {

        $fkName = $this->CreateFkName($one);
        $this->renderTemplate([
            'name' => 'add_fk',
            'code' => "\$this->addForeignKey('{$fkName}', '{$one->table}', '{$one->column}', '{$one->refTable}', '{$one->refColumn}');",
        ]);
    }

    public function migrate()
    {
        // TODO: Implement migrate() method.
    }
}