<?php

/**
 * Display a tip block
 *
 * @package application.helper.smarty
 * @author Rinalds Uzkalns <rinalds@integry.net>
 *
 * @package application.helper.smarty
 */
function smarty_block_allowed($params, $content, LiveCartSmarty $smarty, &$repeat)
{
    if (!$repeat)
    {
        ClassLoader::import('application.helper.AccessStringParser');
        if(AccessStringParser::run($params['role']))
        {
            return $content;
        }
    }
}

?>