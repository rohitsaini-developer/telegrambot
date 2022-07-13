<?php

use think\migration\Migrator;
use think\migration\db\Column;

class TranSaction extends Migrator
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

          $table  =  $this->table('transaction');

          
          $table->addColumn(Column::integer('bot_id')->setComment('Bot Id'))
  
          ->addForeignKey('bot_id', 'master_bot','id',array())

          ->addColumn(Column::integer('user_id')->setComment('User Id'))
  
          ->addForeignKey('user_id', 'tg_tp88user','tuid',array())
  
          ->addColumn(Column::string('ref_no',100)->setDefault(null)->setNullable()->setComment('Reference Number'))
  
          ->addColumn(Column::enum('type', ['deposit', 'withdraw'])->setComment('Transaction Type'))
  
          ->addColumn(Column::decimal('amount', 7, 2)->setDefault(null)->setNullable()->setComment('Transaction amount'))
  
          ->addColumn(Column::longText('description')->setDefault(null)->setNullable()->setComment('Transaction Description'))
          
          ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('1: Success 2: Failed'))
  
          ->addTimestamps() 
  
          ->addIndex(array('ref_no'), array('unique'  =>  true))
  
          ->create();
  
    
    }
}
