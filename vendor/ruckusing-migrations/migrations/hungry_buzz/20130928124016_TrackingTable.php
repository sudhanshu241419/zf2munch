<?php

class TrackingTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	 $tracking_logs = $this->create_table("tracking_logs");
    	 $tracking_logs->column("category", "string");
         $tracking_logs->column("action", "string");
         $tracking_logs->column("user_id", "integer");
         $tracking_logs->column("ip", "string");
         $tracking_logs->column("data", "text");
         $tracking_logs->column("created_at", "datetime");
         $tracking_logs->finish();
    }//up()

    public function down()
    {
    	$this->drop_table("tracking_logs");
    }//down()
}
