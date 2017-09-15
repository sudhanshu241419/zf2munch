<?php

class CreateOwnerResponse extends Ruckusing_Migration_Base
{

    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `owner_response` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `review_id` int(11) NOT NULL,
            `response` text NOT NULL,
            `response_date` datetime NOT NULL,
            PRIMARY KEY (`id`)
        )');
    } // up()
    public function down()
    {} // down()
}
