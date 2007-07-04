<?php

function smarty_modifier_config($key)
{
    return Config::getInstance()->get($key);    
}

?>