<?php

class EncryptCardNumberUserCard extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('ALTER TABLE `user_cards` ADD `encrypt_card_number` VARCHAR( 255 ) NULL AFTER `card_number`');
    }//up()

    public function down()
    {
    }//down()
}
