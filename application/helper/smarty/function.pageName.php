<?php

/**
 * Generates static page title
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_pageName($params, Smarty_Internal_Template $smarty)
{
	if (!class_exists('StaticPage', false))
	{
		ClassLoader::import('application.model.staticpage.StaticPage');
	}

	if (!isset($params['id']))
	{
		return '<span style="color: red; font-weight: bold; font-size: larger;">No static page ID provided</span>';
	}

	$page = StaticPage::getInstanceById($params['id'], StaticPage::LOAD_DATA)->toArray();

	return $page[!empty($params['text']) ? 'text_lang' : 'title_lang'];
}

?>