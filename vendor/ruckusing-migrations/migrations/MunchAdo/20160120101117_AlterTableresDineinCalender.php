<?php

class AlterTableresDineinCalender extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurant_Dinein_calendars` ADD  `breakfast_open` TINYINT NOT NULL DEFAULT  '1' COMMENT  '0=>close,1=>open' AFTER  `restaurant_id`");
        $this->execute("ALTER TABLE  `restaurant_Dinein_calendars` ADD  `lunch_open` TINYINT NOT NULL DEFAULT  '1' COMMENT  '0=>close,1=>open' AFTER `breakfast_end_time`");
        $this->execute("ALTER TABLE  `restaurant_Dinein_calendars` ADD  `dinner_open` TINYINT NOT NULL DEFAULT  '1' COMMENT  '0=>close,1=>open' AFTER  `lunch_end_time`");
    }//up()

    public function down()
    {
    }//down()
}
