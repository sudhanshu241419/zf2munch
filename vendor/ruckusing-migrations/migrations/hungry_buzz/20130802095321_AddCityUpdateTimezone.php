<?php

class AddCityUpdateTimezone extends Ruckusing_Migration_Base
{
    public function up()
    {

    	$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='AL' and country_id=1 and status=1");
    	$this->execute("update `cities` set `time_zone`='America/Anchorage' WHERE state_code='AK' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Phoenix' WHERE state_code='AZ' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='AR' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Los_Angeles' WHERE state_code='CA' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Boise' WHERE state_code='CO' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='CT' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='DE' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='DC' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='FL' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='GA' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='Pacific/Honolulu' WHERE state_code='HI' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Boise' WHERE state_code='ID' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='IL' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='IN' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='IA' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='KS' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='KY' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='LA' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='ME' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='MD' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='MA' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='MI' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='MN' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='MS' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='MO' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Boise' WHERE state_code='MT' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='NE' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Los_Angeles' WHERE state_code='NV' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='NH' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='NJ' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Boise' WHERE state_code='NM' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='NY' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='NC' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='ND' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='OH' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='OK' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Los_Angeles' WHERE state_code='OR' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='PA' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='RI' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='SC' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='SD' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='TN' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='TX' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Boise' WHERE state_code='UT' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='VT' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='VA' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Los_Angeles' WHERE state_code='WA' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/New_York' WHERE state_code='WV' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Chicago' WHERE state_code='WI' and country_id=1 and status=1");
		$this->execute("update `cities` set `time_zone`='America/Boise' WHERE state_code='WY' and country_id=1 and status=1");
    }//up()

    public function down()
    {
    }//down()
}
