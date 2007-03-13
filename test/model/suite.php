<?php
require_once dirname(__FILE__) . '\..\Initialize.php';

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