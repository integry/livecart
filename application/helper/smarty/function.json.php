<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_json($params, LiveCartSmarty $smarty)
{
	$array = $params['array'];
	$assign = isset($params['assign']) ? $params['assign'] : false;

    ClassLoader::import('library.json.json');
    $javaObject = json_encode($array);

    if(!$assign)
    {
        return $javaObject;
    }
    else
    {
        $smarty->assign($assign, $javaObject);
    }
}

?>