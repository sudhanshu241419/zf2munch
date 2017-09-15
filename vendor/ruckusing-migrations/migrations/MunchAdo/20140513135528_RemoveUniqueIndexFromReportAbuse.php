<?php

class RemoveUniqueIndexFromReportAbuse extends Ruckusing_Migration_Base
{
    public function up()
    {
    	//$this->execute("ALTER TABLE report_abuse_restaurants DROP INDEX user_id");
    }//up()

    public function down()
    {
    }//down()
}
