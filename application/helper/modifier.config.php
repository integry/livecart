<?php

function smarty_modifier_config($key, LiveCartSmarty $smarty)
{
    return $smarty->getApplication()->getConfig()->get($key);    
}

?>