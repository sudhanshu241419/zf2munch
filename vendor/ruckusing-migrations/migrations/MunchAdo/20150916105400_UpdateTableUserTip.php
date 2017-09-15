<?php

class UpdateTableUserTip extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('UPDATE `user_tips` set `approved_date`=created_at;');
    }//up()

    public function down()
    {
    }//down()
}
