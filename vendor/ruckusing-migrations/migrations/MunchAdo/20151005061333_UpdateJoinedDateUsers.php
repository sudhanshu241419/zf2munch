<?php

class UpdateJoinedDateUsers extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("update `users` set `created_at` = `update_at` where created_at is NULL");
    }//up()

    public function down()
    {
    }//down()
}
