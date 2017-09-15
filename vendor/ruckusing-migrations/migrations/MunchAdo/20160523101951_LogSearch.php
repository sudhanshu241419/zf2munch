<?php

class LogSearch extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("ALTER TABLE  `log_search` ADD  `device` VARCHAR( 50 ) NOT NULL AFTER  `user_agent`;");
    }//up()

    public function down()
    {
    }//down()
}
