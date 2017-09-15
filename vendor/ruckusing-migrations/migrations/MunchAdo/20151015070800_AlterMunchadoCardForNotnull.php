<?php

class AlterMunchadoCardForNotnull extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `munchado_debit_card` CHANGE `user_id` `user_id` INT( 11 ) UNSIGNED NOT NULL COMMENT 'ref=>users',
CHANGE `card_number` `card_number` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `card_type` `card_type` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `name_on_card` `name_on_card` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `expired_on` `expired_on` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `created_on` `created_on` DATETIME NOT NULL ,
CHANGE `updated_at` `updated_at` DATETIME NOT NULL ,
CHANGE `status` `status` TINYINT( 1 ) NOT NULL COMMENT '1=>active, 2=> deactive'");

$this->execute("ALTER TABLE `munchado_debit_card` ADD UNIQUE (
`card_number`
)");
    }//up()

    public function down()
    {
    }//down()
}
