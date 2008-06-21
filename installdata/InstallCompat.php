<?php

/**
 * Detects specific environment conditions that require changes in LiveCart
 * configuration to operate properly
 */
abstract class InstallCompat
{
	protected $application;

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
	}

	public abstract function IsApplicable();
	public abstract function apply();

	protected function getParsedConfig($what = '')
	{
		ob_start();
		phpinfo();
		$s = ob_get_contents();
		ob_end_clean();
		$a = $mtc = array();
		if (preg_match_all('/<tr><td class="e">(.*?)[ ]*<\/td><td class="v">(.*?)<\/td>(:?<td class="v">(.*?)<\/td>)?<\/tr>/',$s,$mtc,PREG_SET_ORDER))
		{
			foreach($mtc as $v){
				if($v[2] == '<i>no value</i>') continue;
				$a[$v[1]] = $v[2];
			}
		}

		if ($what && isset($a[$what]))
		{
			return $a[$what];
		}

		return $a;
	}
}

?>