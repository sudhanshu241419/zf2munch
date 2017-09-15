<?php

class RenameAuthProviderTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->rename_table("auth_providers", "authentication_channels");
    }//up()

    public function down()
    {
    	$this->rename_table("authentication_channels", "auth_providers");
    }//down()
}
