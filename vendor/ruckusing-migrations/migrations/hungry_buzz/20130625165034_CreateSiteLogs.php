<?php

class CreateSiteLogs extends Ruckusing_Migration_Base
{
    public function up()
    {
    	 $site_log = $this->create_table("site_logs");
    	 $site_log->column("ip_address", "string");
		 $site_log->column("user_agent", "string");
		 $site_log->column("created_at", "datetime");
		 $site_log->finish();

    }//up()

    public function down()
    {
    	$this->drop_table('site_logs');
    }//down()
}
