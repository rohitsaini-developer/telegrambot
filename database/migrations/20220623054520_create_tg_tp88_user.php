<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTgTp88User extends Migrator
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
        $table  =  $this->table('tg_tp88user');
       
        $table->addColumn(Column::integer('bot_id')->setComment('Bot Id'))
        ->addForeignKey('bot_id', 'master_bot','id',array())

        ->addColumn('chat_id', 'string',array('limit'=>64,'comment'=>'Chat Id'))
        ->addColumn('name', 'string',array('limit'=>100,'default'=>Null,'comment'=>'Telegram Name'))
        ->addColumn('number', 'string',array('limit'=>100,'default'=>Null,'comment'=>'User Phone Number'))
        ->addColumn('bank', 'string',array('limit'=>255,'default'=>Null,'comment'=>'Bank Name'))
        ->addColumn('owner', 'string',array('limit'=>191,'default'=>Null,'comment'=>'Owner of Bank'))
        ->addColumn('account', 'string',array('limit'=>20,'default'=>Null,'comment'=>'Account number'))
        ->addColumn(Column::tinyInteger('verify')->setDefault(0)->setComment('0: Not Verified 1: Verified'))
        ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('1: Active 2: Banned 3: Problem'))
        ->addColumn('record', 'text',array('default'=>Null,'comment'=>'Record Verify Time'))
        ->addTimestamps() 
        ->addSoftDelete()  

        ->create();

        //Changes in Id column
        $table->renameColumn('id','tuid');
        // $table->changeColumn(Column::bigInteger('tuid')->setUnsigned()->setComment('User Id'));


    }
}
