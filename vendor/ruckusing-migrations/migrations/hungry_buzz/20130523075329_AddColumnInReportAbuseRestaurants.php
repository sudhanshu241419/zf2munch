<?php

class AddColumnInReportAbuseRestaurants extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table report_abuse_restaurants add column abuse_type enum('duplicate',
    					'offensive','wrong','off-topic','other')");
    	$this->execute("alter table report_abuse_restaurants add column comment varchar(255)");
    }//up()

    public function down()
    {
    }//down()
}
