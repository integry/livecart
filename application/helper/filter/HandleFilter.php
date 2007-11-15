<?php

ClassLoader::import('framework.request.validator.filter.RegexFilter');

/**
 * Generic handle field value filter
 *
 * @package application.helper.filter
 * @author Integry Systems
 */
class HandleFilter extends RegexFilter
{
	public static function create()
	{
		return new RegexFilter('[^-.a-zA-Z0-9]*');
	}
}

?>