<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateMasterBot extends Migrator
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
        $table  =  $this->table('master_bot');
        $table->addColumn('bot_id', 'string',array('limit'=>64,'comment'=>'bot_id'))
        ->addColumn('name', 'string',array('limit'  =>  255,'default'=>Null,'comment'=>'Bot Name'))
        ->addColumn('username', 'string',array('limit'  =>  255,'default'=>Null,'comment'=>'Bot Username'))
        ->addColumn('token', 'text',array('default'=>Null,'comment'=>'Bot Token'))
        ->addTimestamps() 
        ->addSoftDelete()  
        ->addIndex(array('bot_id','username','token'), array('unique'  =>  true,'unique'  =>  true,'unique'  =>  true))
        ->create();

        // $table->changeColumn(Column::bigInteger('id','AUTO_INCREMENT')->setUnsigned());


    }

    /**
     * Migrate Up.
    */
    public function up()
    {

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
