<?php

class UpdateActivityFeedForFeedData extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('UPDATE activity_feed SET feed = REPLACE( feed, \'"review_for":"3"\', \'"review_for":"Dinein"\' ) WHERE 1 =1');
        $this->execute('UPDATE activity_feed SET feed = REPLACE( feed,  \'"review_for":"2"\',  \'"review_for":"Takeout"\' ) WHERE 1 =1');
        $this->execute('UPDATE activity_feed SET feed = REPLACE( feed,  \'"review_for":"1"\',  \'"review_for":"Delivery"\' ) WHERE 1 =1');
    }//up()

    public function down()
    {
    }//down()
}
