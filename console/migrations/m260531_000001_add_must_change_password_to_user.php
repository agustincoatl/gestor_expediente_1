<?php

use yii\db\Migration;

class m260531_000001_add_must_change_password_to_user extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%user}}');
        if ($table && !$table->getColumn('must_change_password')) {
            $this->addColumn('{{%user}}', 'must_change_password', $this->boolean()->notNull()->defaultValue(false)->after('role_id'));
        }
    }

    public function safeDown()
    {
        $table = $this->db->schema->getTableSchema('{{%user}}');
        if ($table && $table->getColumn('must_change_password')) {
            $this->dropColumn('{{%user}}', 'must_change_password');
        }
    }
}
