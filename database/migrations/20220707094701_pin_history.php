<?php

use think\migration\Migrator;
use think\migration\db\Column;

class PinHistory extends Migrator
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
        $table  =  $this->table('pin_history');
        $table->addColumn(Column::integer('bot_id')->setComment('Bot Id'))
        ->addForeignKey('bot_id', 'master_bot','id',array())

        ->addColumn(Column::integer('user_id')->setComment('User Id'))
        ->addForeignKey('user_id', 'tg_tp88user','tuid',array())

        ->addColumn(Column::string('pin_number',100)->setDefault(null)->setNullable()->setComment('PIN Number'))
        ->addColumn(Column::string('code',50)->setDefault(null)->setNullable()->setComment('Code'))
        ->addColumn(Column::decimal('amount',11,2)->setDefault(null)->setNullable()->setComment('amount'))
        ->addColumn(Column::dateTime('pin_time')->setDefault(null)->setNullable()->setComment('Pin Time'))
        ->addColumn(Column::integer('pin_attempt')->setDefault(0)->setComment('Pin Attempt'))
        ->addColumn(Column::enum('status', ['Success', 'Failed','Process']))
        ->addColumn(Column::json('response')->setDefault(null)->setNullable()->setComment('Response'))

        ->addTimestamps()


        ->create();

    }
}
