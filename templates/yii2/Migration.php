<?= '<?php' ?>

use yii\db\Migration;

class <?= $classname ?> extends Migration
{
    public function safeUp()
    {
        <?= $code ?>

    }
    public function safeDown()
    {

    }
}
