<?= '<?php' ?>

class <?= $classname ?> extends CDbMigration
{
    public function safeUp()
    {
        <?= $code ?>

    }
    public function safeDown()
    {

    }
}
