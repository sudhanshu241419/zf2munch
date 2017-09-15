<?php

class ChangeColumnTypeUserFriendsTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->change_column("user_friends","status","boolean") ; 
    }//up()

    public function down()
    {
    	$this->change_column("user_friends","status","string") ;
    }//down()
}
