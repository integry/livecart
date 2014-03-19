<?php


/**
 * Checks if product SKU is unique
 *
 * @package application/helper/check
 * @author Integry Systems 
 */
class IsUniqueSkuCheck extends \Phalcon\Validation\Validator
{
	public function validate(\LiveCartValidator $validator, $field)
	{
 		$sku = $validator->getValue($field);
 		
 		$prod = \product\Product::getInstanceBySKU($sku);

        if ($prod)
        {
            $validator->appendMessage(new \Phalcon\Validation\Message($this->getOption('message'), $field));
            return false;
        }

        return true;
	}
}

?>
