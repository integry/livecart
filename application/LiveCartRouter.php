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

	private $langFields = array();

	private $langIDs = array();

	public function createUrlFromRoute($route, $isXHtml = false)
	{
		preg_match('/^([a-z]{2})\//', $route, $match);
		$varSuffix = isset($match[1]) ? '_' . $match[1] : '';

		foreach ($this->langReplaces as $needle => $replace)
		{
			$repl = !empty($replace['record'][$replace['field'] . $varSuffix]) ? $replace['record'][$replace['field'] . $varSuffix] : (isset($replace['record'][$replace['field']]) ? $replace['record'][$replace['field']] : '');
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
		$this->langReplaces[$value] = array('field' => $field, 'record' => array_intersect_key($record, $this->getLangFields($field)));
	}

	private function getLanguageIDs()
	{
		if (!$this->langIDs)
		{
			$this->langIDs = ActiveRecordModel::getApplication()->getLanguageArray(true);
		}

		return $this->langIDs;
	}

	private function getLangFields($field)
	{
		if (!isset($this->langFields[$field]))
		{
			$this->langFields[$field] = array();
			foreach ($this->getLanguageIDs() as $id)
			{
				$this->langFields[$field][$field . '_' . $id] = true;
			}
		}

		return $this->langFields[$field];
	}
}

?>