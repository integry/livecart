<?php

function smarty_modifier_config($key)
{
    return Config::getInstance()->getValue($key);    
}

?>