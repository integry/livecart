<?php

/**
 * Registry class to store global variables of Application instead of using $_GLOBAL
 *
 * @author Denis Slaveckij <denis@integry.net>
 */
class Registry
{

	private static $registry = array();
	/**
	 * Gets value from registry.
	 * @param string $key Key of value
	 * @return mixed Value
	 */
	public static function getValue($key)
	{
		$value = isSet(self::$registry[$key]) ? self::$registry[$key]: false;
		return $value;
	}

	/**
	 * Sets value from registry.
	 * @param string $key Key of value
	 * @param mixed $value Value
	 */
	public static function setValue($key, $value)
	{
		self::$registry[$key] = $value;
	}
}

?>
