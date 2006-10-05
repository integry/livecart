<?php

/**
 * Smarty block plugin, for creating tabcontrol. It's used together with tabpage plugin.
 *
 * @param array $params Possible params of tabcontrol:
 *  'class' =>
 *  'visible' => visible tab of tabcontrol (not neccessary). Then the site context is reloaded next time, tabcontrol
 * @param Smarty $smarty
 * @param $repeat
 * @return string
 *
 * <code>
 * {tabcontrol style="height: 235px; width: 500px; border: 1px solid #000000;"}
 * 		{tabpage title="First" class="tab_class"}
 *       	...First content...
 *	 	{/tabpage}
 * 		{tabpage title="First"}
 *			...Second content...
 *	 	{/tabpage}
 * {/tabcontrol}
 * </code>
 *
 * @package application.helper
 * @package application.helper
 */
function smarty_block_tabcontrol($params, $content, Smarty $smarty, &$repeat) {
	
	Registry::setValue('tab_md5', md5($_SERVER['QUERY_STRING']));
	$control = (int)Registry::getValue('tab_controls');
	$current = (int)Registry::getValue('tab_pages');		

	$js = $smarty->get_template_vars('JAVASCRIPT');

	if (empty($js) || !in_array('javascript/document.js', $js)) {
		
		$smarty->append("JAVASCRIPT", 'javascript/document.js');		
	}
		
	if (empty($js) || !in_array('javascript/tabcontrol.js', $js)) {
		
		$smarty->append("JAVASCRIPT", 'javascript/tabcontrol.js');	
	}
	///////////////////////////	
	
			
	if ($repeat) {
			
	  	$smarty->assign('TABCONTROL_TOP', '');		
	  	Registry::setValue('tab_pages', 0);
	} else {	  	  
				
		// If temlate changed, ant current pages value is less than previous
		/*if (isSet($_COOKIE['TabPageCurrent_'.Registry::getValue('tab_md5')]) 
			&& isSet($_COOKIE['TabPageCurrent_'.Registry::getValue('tab_md5')][$control]) 
			&& (int)Registry::getValue('tab_pages') <= $_COOKIE['TabPageCurrent_'.Registry::getValue('tab_md5')][(int)$control]		
			) {		  
			
			$smarty->append("BODY_ONLOAD", " alert(\"adsf\"); ");
		}*/
		
		Registry::setValue('tab_controls', (int)Registry::getValue('tab_controls') + 1);	
	}				
				
	$block = '<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td>
			<table style="border-collapse: collapse" >
				<tr> 
					'.$smarty->get_template_vars('TABCONTROL_TOP').'
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			'.$content
			.'<div style="position: relative; visibility: hidden;" class="'.$params['class'].'"></div>
		</td>
	</tr>
</table>';	

	return $block;	
}

?>