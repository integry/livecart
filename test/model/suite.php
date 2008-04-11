<?php

/**
 * @author Integry Systems
 * @package test.model
 */

require_once dirname(dirname(__FILE__)) . '/Initialize.php';

define('TEST_SUITE', true);
class Suite extends UTGroupTest
{
	public function __construct()
	{
		parent::__construct('All Livecart Tests');
		$this->addDir(dirname(__FILE__));
	}
}
?>