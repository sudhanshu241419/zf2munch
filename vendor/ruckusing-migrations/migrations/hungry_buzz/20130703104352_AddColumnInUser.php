<?php

class AddColumnInUser extends Ruckusing_Migration_Base
{
    public function up()
    {
		$this->add_column("users", "delevary_instructions", "text");
		$this->add_column("users", "takeout_instructions", "text");
    }//up()

    public function down()
    {
		$this->remove_column("users", "delevary_instructions");
		$this->remove_column("users", "takeout_instructions");
    }//down)
}
