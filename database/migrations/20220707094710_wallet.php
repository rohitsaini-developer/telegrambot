<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Wallet extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {

            // create the table
            $table  =  $this->table('wallet');
            $table->addColumn(Column::integer('bot_id')->setComment('Bot Id'))
            ->addForeignKey('bot_id', 'master_bot','id',array())

            ->addColumn('chat_id', 'string',array('limit'=>64,'comment'=>'Chat Id'))

            ->addColumn(Column::integer('pin_id')->setComment('Pin Id'))
            ->addForeignKey('pin_id', 'pin_history','id',array())

            ->addColumn(Column::decimal('amount',11,2)->setDefault(null)->setNullable()->setComment('amount'))
            ->addTimestamps()
    
    
            ->create();
    }
}
