<?php

class AlterActivityFeedColumnDatatype extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `activity_feed` CHANGE `feed` `feed` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `feed_for_others` `feed_for_others` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;");
        
    }//up()

    public function down()
    {
    }//down()
}
