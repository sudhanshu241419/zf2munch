<?php

class AlterUserInstructionUserReservation extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_reservations` CHANGE  `user_instruction`  `user_instruction` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
