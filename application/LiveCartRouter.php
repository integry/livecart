<?php

ClassLoader::import('framework.request.Router');

/**
 *  Implements LiveCart-specific routing logic
 *
 *  @package application
 *  @author Integry Systems
 */
class LiveCartRouter extends Router
{
	private $langReplaces = array();

	public function createURL($URLParamList, $isXHtml = false)
	{
		return parent::createURL($URLParamList, $isXHtml);
	}

	public function createUrlFromRoute($route, $isXHtml = false)
	{
		preg_match('/^([a-z]{2})\//', $route, $match);
		$varSuffix = isset($match[1]) ? '_' . $match[1] : '';

		foreach ($this->langReplaces as $needle => $replace)
		{
			$repl = !empty($replace['record'][$replace['field'] . $varSuffix]) ? $replace['record'][$replace['field'] . $varSuffix] : $replace['record'][$replace['field']];
			if ($repl)
			{
				$repl = createHandleString($repl);
				if ($repl != $needle)
				{
					$route = str_replace($needle, $repl, $route);
				}
			}
		}

		return parent::createUrlFromRoute($route, $isXHtml);
	}

	public function setLangReplace($value, $field, $record)
	{
		$this->langReplaces[$value] = array('field' => $field, 'record' => $record);
	}
}

?>