<?php

class ModificationInRestaurantFeatureTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("UPDATE `features` SET `features` = 'Breakfast', `feature_type` = 'Restaurant Features' WHERE `features`.`features` = 'breakfast'");
    	$this->execute("UPDATE `features` SET `features` = 'Lunch', `feature_type` = 'Restaurant Features' WHERE `features`.`features` = 'lunch'");
    	$this->execute("UPDATE `features` SET `features` = 'Dinner', `feature_type` = 'Restaurant Features' WHERE `features`.`features` = 'dinner'");
    	$this->execute("DELETE FROM `features` WHERE `features`.`features` = 'sentiments'");
    }//up()

    public function down()
    {
    }//down()
}
