<?php


/**
 * Product data feed
 *
 * @author Integry Systems
 * @package application/controller
 */
class ProductFeed extends ARFeed
{
	protected $productFilter;

	public function __construct(ProductFilter $filter)
	{
		$this->productFilter = $filter;

		parent::__construct($filter->getSelectFilter(), 'Product', array('Category', 'ProductImage', 'Manufacturer'));
	}

	public function setLimit($size)
	{
		$this->size = $size;
	}

	protected function postProcessData()
	{
		ProductPrice::loadPricesForRecordSetArray($this->data);
		ProductSpecification::loadSpecificationForRecordSetArray($this->data, true);
		Product::loadCategoryPathsForArray($this->data);

		foreach ($this->data as $key => $product)
		{
			$this->data[$key]['name_lang_utf8'] = $this->getFixedUtf8($product['name_lang']);
			$this->data[$key]['shortDescription_lang_utf8'] = $this->getFixedUtf8($product['shortDescription_lang']);
			$this->data[$key]['longDescription_lang_utf8'] = $this->getFixedUtf8($product['longDescription_lang']);

			$this->data[$key]['name_lang_safe'] = $this->getSafeEncoding($product['name_lang']);
			$this->data[$key]['shortDescription_lang_safe'] = $this->getSafeEncoding($product['shortDescription_lang']);
			$this->data[$key]['longDescription_lang_safe'] = $this->getSafeEncoding($product['longDescription_lang']);
		}
	}

	protected function getFixedUtf8($string)
	{
		static $trans_array = array();

		if (!$trans_array)
		{
			for ($i=127; $i<255; $i++)
			{
				$trans_array[chr($i)] = "&#" . $i . ";";
				$trans_array[chr($i)] = '';
			}
		}

		$string = trim(strip_tags($string));

		$string = utf8_encode($string);
		$string = strtr($string, $trans_array);
		$string = htmlentities($string);

		return $string;
	}

	protected function getSafeEncoding($string)
	{
		$string = iconv("UTF-8", "ISO-8859-1//IGNORE", $string);
		$string = iconv("ISO-8859-1", "UTF-8", $string);

		return $string;
	}

	protected function isValidUTF8($str)
	{
		// values of -1 represent disalloweded values for the first bytes in current UTF-8
		static $trailing_bytes = array (
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
			-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
			-1,-1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, 1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
			2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, 3,3,3,3,3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1
		);

		$ups = unpack('C*', $str);
		if (!($aCnt = count($ups))) return true; // Empty string *is* valid UTF-8
		for ($i = 1; $i <= $aCnt;)
		{
			if (!($tbytes = $trailing_bytes[($b1 = $ups[$i++])])) continue;
			if ($tbytes == -1) return false;

			$first = true;
			while ($tbytes > 0 && $i <= $aCnt)
			{
				$cbyte = $ups[$i++];
				if (($cbyte & 0xC0) != 0x80) return false;

				if ($first)
				{
					switch ($b1)
					{
						case 0xE0:
							if ($cbyte < 0xA0) return false;
							break;
						case 0xED:
							if ($cbyte > 0x9F) return false;
							break;
						case 0xF0:
							if ($cbyte < 0x90) return false;
							break;
						case 0xF4:
							if ($cbyte > 0x8F) return false;
							break;
						default:
							break;
					}
					$first = false;
				}
				$tbytes--;
			}
			if ($tbytes) return false; // incomplete sequence at EOS
		}
		return true;
	}
}

?>
