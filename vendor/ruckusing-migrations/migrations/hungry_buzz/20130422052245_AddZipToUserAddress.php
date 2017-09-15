<?php

class AddZipToUserAddress extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("user_addresses", "zip", "integer");
    	$this->rename_column("user_addresses", "address_name", "address");
    }//up()

    public function down()
    {
    	$this->remove_column("user_addresses", "zip");
    	$this->rename_column("user_addresses", "address", "address_name");
    }//down()
}
