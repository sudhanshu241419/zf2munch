<?php

class AddValuesInFeatures extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("INSERT INTO `features` (
     `features` ,
     `feature_type` ,
     `features_key` ,
     `feature_desc` ,
     `status`
    )
     VALUES ('breakfast', '', '', '1', '1'),
              ('lunch', '', '', '1', '1'),
              ('dinner', '', '', '1', '1')") ;

   }//up()

    public function down()
    {
    }//down()
}
