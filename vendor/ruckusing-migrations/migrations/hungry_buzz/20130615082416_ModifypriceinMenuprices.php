<?php

class ModifypriceinMenuprices extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE menu_prices change price price float DEFAULT 0.0");
    }//up()

    public function down()
    {
    	$this->execute("ALTER TABLE menu_prices change price price varchar(20)");
    }//down()
}
