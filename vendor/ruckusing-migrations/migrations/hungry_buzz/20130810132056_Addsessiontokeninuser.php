<?php

class Addsessiontokeninuser extends Ruckusing_Migration_Base
{
    public function up()
    {

    	$this->add_column('users', 'session_token', 'string', array('length' => 30));

    }//up()

    public function down()
    {
    	$this->remove_column('users', 'session_token');
    }//down()
}
