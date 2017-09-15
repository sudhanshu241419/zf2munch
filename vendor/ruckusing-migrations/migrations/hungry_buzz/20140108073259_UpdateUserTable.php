<?php

class UpdateUserTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("UPDATE `users` u JOIN `authentication_channels` a ON u.email = a.email SET u.session_token = a.uid");
    }//up()

    public function down()
    {
    }//down()
}
