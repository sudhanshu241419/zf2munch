<?php

class AlterReportAbuseRestaurantsForAbusetype extends Ruckusing_Migration_Base
{
    public function up()
    {
       $this->execute("ALTER TABLE `report_abuse_restaurants` CHANGE `abuse_type` `abuse_type` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
    }//up()

    public function down()
    {
    }//down()
}
