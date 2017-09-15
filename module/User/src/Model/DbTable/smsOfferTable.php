<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class smsOfferTable extends AbstractDbTable {
	protected $_table_name = "sms_offer";
	protected $_array_object_prototype = 'User\Model\SmsOffer';
}