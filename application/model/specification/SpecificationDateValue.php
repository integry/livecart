<?php

/**
 * Date attribute value assigned to a particular product.
 * 
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>   
 */
class SpecificationDateValue extends ValueSpecification
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("SpecificationDateValue");

		parent::defineSchema($className);		  	
		$schema->registerField(new ARField("value", ARDate::instance()));
	}

	public static function getNewInstance(Product $product, SpecField $field, $value)
	{
		return parent::getNewInstance(__CLASS__, $product, $field, $value);
	}
	
	public static function restoreInstance(Product $product, SpecField $field, $value)
	{
		return parent::restoreInstance(__CLASS__, $product, $field, $value);
	}
	
	public function toArray()
	{
		$array = parent::toArray();
		
		if ($array['value'])
		{
	    	$dateTransform = array
	    	(		
	    		'time_full' => Locale::FORMAT_TIME_FULL,
	    		'time_long' => Locale::FORMAT_TIME_LONG,
	    		'time_medium' => Locale::FORMAT_TIME_MEDIUM,
	    		'time_short' => Locale::FORMAT_TIME_SHORT,
	    		'date_full' => Locale::FORMAT_DATE_FULL,
	    		'date_long' => Locale::FORMAT_DATE_LONG,
	    		'date_medium' => Locale::FORMAT_DATE_MEDIUM,
	    		'date_short' => Locale::FORMAT_DATE_SHORT,		
	    	);
	
			$locale = Locale::getCurrentLocale();

			$time = strtotime($array['value']);
	
			$res = array();						
			foreach ($dateTransform as $format => $code)
			{
				$res[$format] = $locale->getFormattedTime($time, $code);
			}
				
			$array['formatted'] = $res;
		}
			
		return $array;
	}	
}

?>