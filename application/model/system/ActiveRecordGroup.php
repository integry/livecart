<?php

/**
 *
 * @package application/model/system
 * @author Integry Systems <http://integry.com>
 */
class ActiveRecordGroup
{
	public static function mergeGroupsWithFields($className, $groups, $fields)
	{
		$fieldsWithGroups = array();
		$k = $i = 1;
		$fieldsCount = count($fields);
		foreach($fields as $field)
		{
			if(isset($field[$className]) && !empty($field[$className]['ID']))
			{
				$shiftGroup = false;
				while($group = array_shift($groups))
				{
					if($group['position'] < $field[$className]['position'])
					{
						$shiftGroup = true;
						$fieldsWithGroups[$i++] = array($className => $group);
					}
					else
					{
						if($group['position'] > $field[$className]['position'])
						{
							array_unshift($groups, $group);
						}
						break;
					}
		 		}
			}

			$fieldsWithGroups[$i++] = $field;
			$k++;
	   }

	   while($group = array_shift($groups))
	   {
		   $fieldsWithGroups[$i++] = array($className => $group);
	   }

	   return $fieldsWithGroups;
	}
}

?>