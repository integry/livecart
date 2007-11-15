<?php

/**
 *  Provides additional functionality to strtotime()
 *
 *  For example:
 *	  
 *	  w:Monday - this week's Monday
 *	  w:Monday ~ -1 week - last week's Monday
 *
 *  @param string $date Date string
 *  @package application.helper
 *  @author Integry Systems
 */
function getDateFromString($date, $now = null)
{
	$now = $now ? $now : 'now';
		
	if ('w:' == substr($date, 0, 2))
	{
		if (strpos($date, ' ~ '))
		{
			list($date, $now) = explode(' ~ ', $date);
		}
		
		$day = substr($date, 2);
		
		$rel = date("N", strtotime($day)) - date("N");
		
		$time = strtotime("$rel days", strtotime($now));
		
		return strtotime(date("Y-m-d", $time));
	}	
	
	return strtotime($date, strtotime($now));
}

?>