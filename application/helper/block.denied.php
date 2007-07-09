<?php
/**
 * Display a tip block
 *
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 *
 * @package application.helper
 */
function smarty_block_denied($params, $content, LiveCartSmarty $smarty, &$repeat)
{
    if (!$repeat)
    {
        ClassLoader::import('application.helper.AccessStringParser');
        if(!AccessStringParser::run($params['role']))
        {
            return $content;
        }
    }
}
?>