<?php

class Updatepriorityfieldsmigration extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("Update cuisines set priority = 1 where cuisine = 'American'");
    	$this->execute("Update cuisines set priority = 2 where cuisine = 'Mexican'");
    	$this->execute("Update cuisines set priority = 3 where cuisine = 'Brazilian'");
    	$this->execute("Update cuisines set priority = 4 where cuisine = 'Peruvian'");
    	$this->execute("Update cuisines set priority = 5 where cuisine = 'Caribbean'");
    	$this->execute("Update cuisines set priority = 6 where cuisine = 'New England'");
    	$this->execute("Update cuisines set priority = 7 where cuisine = 'Nuevo Latino'");
    	$this->execute("Update cuisines set priority = 8 where cuisine = 'Chinese'");
    	$this->execute("Update cuisines set priority = 9 where cuisine = 'Japanese'");
    	$this->execute("Update cuisines set priority = 10 where cuisine = 'Indian'");
    	$this->execute("Update cuisines set priority = 11 where cuisine = 'Middle Eastern'");
    	$this->execute("Update cuisines set priority = 12 where cuisine = 'Thai'");
    	$this->execute("Update cuisines set priority = 13 where cuisine = 'Vietnamese'");
    	$this->execute("Update cuisines set priority = 14 where cuisine = 'Asian'");
    	$this->execute("Update cuisines set priority = 15 where cuisine = 'Italian'");
    	$this->execute("Update cuisines set priority = 16 where cuisine = 'French'");
    	$this->execute("Update cuisines set priority = 17 where cuisine = 'German'");
    	$this->execute("Update cuisines set priority = 18 where cuisine = 'British'");
    	$this->execute("Update cuisines set priority = 19 where cuisine = 'European'");
    	$this->execute("Update cuisines set priority = 20 where cuisine = 'Spanish'");
    	$this->execute("Update cuisines set priority = 21 where cuisine = 'Turkish'");
    	$this->execute("Update cuisines set priority = 22 where cuisine = 'African'");
    	$this->execute("Update cuisines set priority = 23 where cuisine = 'Moroccan'");
    	$this->execute("Update cuisines set priority = 24 where cuisine = 'Ethiopian'");
    }//up()

    public function down()
    {
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'American'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Mexican'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Brazilian'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Peruvian'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Caribbean'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'New England'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Nuevo Latino'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Chinese'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Japanese'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Indian'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Middle Eastern'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Thai'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Vietnamese'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Asian'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Italian'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'French'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'German'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'British'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'European'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Spanish'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Turkish'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'African'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Moroccan'");
    	$this->execute("Update cuisines set priority = 0 where cuisine = 'Ethiopian'");
    }//down()
}
