<?php

namespace Authenticationchanel\Model;

use MCommons\Model\AbstractModel;

class Authenticationchanel extends AbstractModel {
	public $id;
	public $user_id;
	public $channel;
	public $uid;
	public $name;
	public $email;
	public $display_pic_url;
	public $access_token;
	public $response;
	public $permissions;
	public $created_at;
	public $updated_at;
	protected $_db_table_name = 'Authenticationchanel\Model\DbTable\AuthenticationchanelTable';
	protected $_primary_key = 'id';
	const FACEBOOK = 'facebook';
	const TWITTER = 'twitter';
	const FORM = 'form';
	public function create(array $options = array()) {
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$rowsAffected = $writeGateway->insert ( $options );
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
	}
}