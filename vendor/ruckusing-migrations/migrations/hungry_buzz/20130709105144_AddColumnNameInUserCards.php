<?php

class AddColumnNameInUserCards extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_cards` ADD `card_type` VARCHAR( 50 ) NOT NULL AFTER `card_number`");
    }//up()

    public function down()
    {
     $this->remove_column("user_cards", "card_type");
    }//down()
}
