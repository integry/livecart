<?php

/**
 * Creates more complex translation strings that depend on and include numeric variables
 *
 * <code>
 *      {maketext text="There are [quant,_1,item,items,no items] in your shopping basket." params=$cnt}
 *      {maketext text="Displaying [_1] to [_2] of [_3] found orders." params=$from,$to,$count}
 * </code>
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_maketext($params, LiveCartSmarty $smarty) 
{	
	return $smarty->getApplication()->makeText($params['text'], $params['params']);
}

?>