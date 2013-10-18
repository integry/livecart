<?php

namespace sitenews;

/**
 * News post entry
 *
 * @package application/model/news
 * @author Integry Systems <http://integry.com>
 */
class NewsPost extends \system\MultilingualObject
{
	public $ID;
	public $isEnabled;
	public $position;
	public $time;
	public $title;
	public $text;
	public $moreText;

	/*
	public function beforeCreate()
	{
	  	$this->setLastPosition();

	}
	*/
}

?>