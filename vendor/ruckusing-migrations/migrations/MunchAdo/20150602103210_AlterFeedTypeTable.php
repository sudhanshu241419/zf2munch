<?php

class AlterFeedTypeTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `activity_feed` CHANGE `feed_type` `feed_type_id` INT( 11 ) NOT NULL ;");
    }//up()

    public function down()
    {
    }//down()
}
