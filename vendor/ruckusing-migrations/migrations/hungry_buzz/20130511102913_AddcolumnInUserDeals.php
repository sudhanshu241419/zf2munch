<?php

class AddcolumnInUserDeals extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table user_deals add column quantity int(5) default 0");
    	$this->execute("alter table user_deals change type type enum('d','c') default null");
    }//up()

    public function down()
    {
    }//down()
}
