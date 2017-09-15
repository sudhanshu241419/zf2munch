<?php

class AddColumnInFeatureTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	 $this->add_column('features', "features_key", "string"); 
    }//up()

    public function down()
    {
    	  $this->remove_column('features', 'features_key'); 
    }//down()
}
