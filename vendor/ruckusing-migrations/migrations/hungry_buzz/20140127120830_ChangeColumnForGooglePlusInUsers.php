<?php

class ChangeColumnForGooglePlusInUsers extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `users` CHANGE `user_source` `user_source` ENUM( 'fb', 'tw', 'ws', 'gp' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
