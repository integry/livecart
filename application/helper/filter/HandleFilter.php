<?php


/**
 * Generic handle field value filter
 *
 * @package application/helper/filter
 * @author Integry Systems
 */
class HandleFilter extends RegexFilter
{
	public static function create()
	{
		return new RegexFilter('[\/]*');
	}
}

?>