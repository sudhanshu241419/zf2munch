<?php

class UpdateUserOrderForAddress extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE user_orders SET address = delivery_address WHERE address IS NULL");
    }//up()

    public function down()
    {
    }//down()
}
