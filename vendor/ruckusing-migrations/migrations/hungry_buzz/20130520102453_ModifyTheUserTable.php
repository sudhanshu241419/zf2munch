<?php

class ModifyTheUserTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE users CHANGE mobile mobile varchar(20)");
    	$this->execute("ALTER TABLE users CHANGE phone phone varchar(20)");

    }//up()

    public function down()
    {
    	$this->execute("ALTER TABLE users CHANGE mobile mobile varchar(20) NOT NULL");
    	$this->execute("ALTER TABLE users CHANGE phone phone varchar(20) NOT NULL");
    }//down()
}
