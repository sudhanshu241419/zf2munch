<?php

class Alteruidfieldinsocialfeed extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table social_feeds change u_id u_id varchar(255)");
    }//up()

    public function down()
    {
    	$this->execute("alter table social_feeds change u_id u_id int(11)");
    }//down()
}
