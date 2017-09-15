<?php

class AddTutorialToUsers extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `users` ADD `tutorial` TEXT NULL DEFAULT NULL');
    }//up()

    public function down()
    {
    }//down()
}
