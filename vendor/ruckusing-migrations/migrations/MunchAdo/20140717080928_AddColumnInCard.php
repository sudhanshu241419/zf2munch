<?php

class AddColumnInCard extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `user_cards` ADD `zipcode` VARCHAR( 50 ) NULL DEFAULT NULL ');
    }//up()

    public function down()
    {
    }//down()
}
