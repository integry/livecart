<?php

/**
 * Display a tip block
 *
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 *
 * @package application.helper
 */
function smarty_block_allowed($params, $content, $smarty, &$repeat)
{
    if (!$repeat)
    {
        ClassLoader::import('application.model.user.User');
        $currentUser = User::getCurrentUser();
        if($currentUser->hasAccess($params['role']))
        {
            return $content;
        }
    }
}

?>