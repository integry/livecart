<?php
require_once '../Initialize.php';

class Suite extends UTGroupTest
{
    public function __construct()
    {
        parent::__construct('All Livecart Tests');
        $this->addDir(getcwd());
    }
}
?>