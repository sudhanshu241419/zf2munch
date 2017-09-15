<?php

class AlterTableMunchadoDebitCard extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `munchado_debit_card` ADD  `zipcode` VARCHAR( 50 ) NOT NULL AFTER  `expired_on`");
        $this->execute("ALTER TABLE  `munchado_debit_card` ADD  `cvv` VARCHAR( 20 ) NOT NULL AFTER  `card_number`");
    }//up()

    public function down()
    {
    }//down()
}
