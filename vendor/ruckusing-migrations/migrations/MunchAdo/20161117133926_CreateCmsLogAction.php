<?php

class CreateCmsLogAction extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute(" INSERT INTO `cms_log_action` ( `action_name`, `isactive`) VALUES ('Create', '1'), ('update', '1'), ('delete', '1'), ('login', '1'), ('logout', '1'), ('upload', '1'), ('assign', '1'), ('rejected', '1'), ('live', '1');");
    }//up()

    public function down()
    {
    }//down()
}
