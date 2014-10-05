<?php

use Phinx\Migration\AbstractMigration;

class Init extends AbstractMigration
{
	public function change()
	{
		$table = $this->table('locker', ['id' => true, 'primary_key' => ['id']]);
		$table
			->addColumn('uid', 'string', ['limit' => 64, 'default' => false])
			->addColumn('timestamp', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
			->addIndex(['uid'])
			->save();

		$table = $this->table('mail_ids', ['id' => true, 'primary_key' => ['id']]);
		$table
			->addColumn('mail_id', 'string', ['limit' => 255])
			->addColumn('mail_uniq_id', 'string', ['limit' => 255])
			->addColumn('date_read', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
			->addIndex(['mail_id'])
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