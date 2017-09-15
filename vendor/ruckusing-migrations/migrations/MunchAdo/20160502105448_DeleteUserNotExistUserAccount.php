<?php

class DeleteUserNotExistUserAccount extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("DELETE FROM user_account WHERE (SELECT count(users.id) FROM users WHERE id = user_account.user_id) < 1");
    }//up()

    public function down()
    {
    }//down()
}
