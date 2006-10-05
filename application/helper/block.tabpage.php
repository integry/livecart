<?php

/**
 * Smarty block plugin, for creating tabpage. It's used in tabcontol block.
 * Possible params of tabpage:
 *  'class' => css class of tabpage's menu
 *  'selectedclass' => css clas of selected tabpage's menu
 *  'title' => Title of tabpage
 *
 * @package application.helper
 */
function smarty_block_tabpage($params, $content, Smarty $smarty, &$repeat) {
		
	$control = (int)Registry::getValue('tab_controls');
	$current = (int)Registry::getValue('tab_pages');
	
	$tabcontrol_params = $smarty->_tag_stack[0][1];	
	
	if (isSet($_COOKIE['TabPageCurrent_'.Registry::getValue('tab_md5')]) && isSet($_COOKIE['TabPageCurrent_'.Registry::getValue('tab_md5')][$control])) {			
		
		$tabcontrol_params['visible'] = (int)$_COOKIE['TabPageCurrent_'.Registry::getValue('tab_md5')	  ][$control];	
	}
		
	$visible = !empty($tabcontrol_params['visible']) ? $tabcontrol_params['visible'] : 0;		
	
	$visibility = $current == $visible? 'visible' : 'hidden';
	$selected_class = ($current == $visible && !empty($params['selectedclass']))? $params['selectedclass'] : $params['class'];
	$change_class = !empty($params['selectedclass'])? $params['selectedclass'] : $params['class'];
			
	if ($repeat) {				
		
		$smarty->assign('TABCONTROL_TOP', $smarty->get_template_vars('TABCONTROL_TOP').
'<td id="tab_td_'.$control.'_'.$current.'" class="'.$selected_class.'"  onclick="tab_changed('.$control.', '.$current.', '.$visible.', \''.Registry::getValue('tab_md5').'\', \''.$params['class'].'\',  \''.$change_class.'\');">			
	'.$params['title'].'
</td>');
	} else {	  	  
	
		Registry::setValue('tab_pages', (int)Registry::getValue('tab_pages') + 1);	
	}		
		
	return '<div id="tab_page_'.$control.'_'.$current.'" style="position: absolute; overflow: auto; visibility: '.$visibility.';" class="'.$tabcontrol_params['class'].'">'.$content.'</div>';	
}

?>