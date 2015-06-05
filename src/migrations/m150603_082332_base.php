<?php

use yii\db\Schema;
use yii\db\Migration;

class m150603_082332_base extends Migration
{
    public function up()
    {
        $this->insert(
            '{{%configurable}}',
            [
                'module' => 'OpenGraph',
                'sort_order' => 12,
                'section_name' => 'Open graph',
                'display_in_config' => 1,
            ]
        );

        $this->createTable(
            '{{%object_open_graph}}',
            [
                'id' => Schema::TYPE_PK,
                'object_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'object_model_id' => Schema::TYPE_INTEGER .' NOT NULL',
                'active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'title' => Schema::TYPE_STRING.' NOT NULL',
                'description' => Schema::TYPE_TEXT.' NOT NULL',
                'image' => Schema::TYPE_STRING
            ]
        );
    }

    public function down()
    {
        $this->delete('{{%configurable}}', ['module' => 'OpenGraph']);
        $this->dropTable('{{%object_open_graph}}');
    }
}
