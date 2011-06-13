<?php
/**
 *
 * @package library.json
 * @author Integry Systems
 */

if (!function_exists('json_encode'))
{
	function json_encode($value)
	{
		if (!class_exists('Services_JSON', false))
		{
		  	include 'Services_JSON.php';
		}

		$inst = new Services_JSON();
		return $inst->encode($value);
	}

	function json_decode($value)
	{
		if (!class_exists('Services_JSON', false))
		{
		  	include 'Services_JSON.php';
		}

		$inst = new Services_JSON();
		return $inst->decode($value);
	}
}

?>