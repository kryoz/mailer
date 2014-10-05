<?php

use Phinx\Migration\AbstractMigration;

class MailInfo extends AbstractMigration
{

    public function change()
    {
	    $table = $this->table('mail_info', ['id' => true, 'primary_key' => ['id']]);
	    $table
		    ->addColumn('mail_id', 'integer')
		    ->addColumn('email', 'string', ['limit' => 128])
		    ->addColumn('name', 'string', ['limit' => 128])
		    ->addColumn('city', 'string', ['limit' => 128])
		    ->addColumn('subject_id', 'string', ['limit' => 32])
		    ->addColumn('comment', 'string', ['limit' => 1024])
		    ->addColumn('message_date', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
		    ->addColumn('is_sent', 'boolean', ['default' => false, 'null' => false])
		    ->addColumn('fail_count', 'integer', ['default' => 0, 'null' => false])
		    ->addForeignKey('mail_id', 'mail_ids', 'id', ['delete'=> 'CASCADE'])
		    ->save();
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