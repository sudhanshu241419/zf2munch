<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Form\Element;

class RestaurantImage extends AbstractModel {
	public function __construct($name = null, $options = array()) {
		parent::__construct ( $name, $options );
		$this->addElements ();
	}
	public function addElements() {
		// File Input
		$file = new Element ( 'image-file' );
		$file->setLabel ( 'Avatar Image Upload' )->setAttribute ( 'id', 'image-file' );
		$this->add ( $file );
	}
}
