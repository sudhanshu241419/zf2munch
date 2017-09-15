<?php

class AddColumnSentimentInUserreview extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_reviews` ADD `sentiment` TINYINT NULL AFTER `approved_by` ");
    }//up()

    public function down()
    {
    	$this->remove_column("user_reviews","sentiment");
    }//down()
}
