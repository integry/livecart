<?php

/**
 * Display a tip block
 *
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 *
 * @package application.helper
 */
function smarty_block_role($params, $content, $smarty, &$repeat)
{
    if (!$repeat)
    {
        $currentUser = User::getCurrentUser();
        
        if(!$currentUser->hasAccess($params['name']))
        {
            return $content;
        }
        else
        {
            // Show some message that user has no permission to view this content
        }

    }
}

?>