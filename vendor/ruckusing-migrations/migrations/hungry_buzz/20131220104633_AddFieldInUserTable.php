<?php

class AddFieldInUserTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE  `users` ADD  `last_login` TIMESTAMP NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
