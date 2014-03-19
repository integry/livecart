<?php

/**
 * Creates a handle string that is usually used as part of URL to uniquely
 * identify some record.
 *
 * Basically it simply removes reserved URL characters and does some basic formatting
 *
 * @param string $str
 * @return string
 * @package application/helper
 * @author Integry Systems
 * @todo test with multibyte strings
 */
class CreateHandleString
{
	public static function create($str)
	{
		static $cache = array();

		static $replaceSpecialChars = null;

		static $table = array(
			'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
			'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
			'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
			'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
			'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',

			// latvian
			'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k',
			'ļ' => 'l', 'ņ' => 'n', 'ō' => 'o', 'ŗ' => 'r', 'š' => 's', 'ū' => 'u', 'ž' => 'z', 'Ā' => 'A',
			'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'I', 'Ķ' => 'K', 'Ļ' => 'L', 'Ņ' => 'N', 'Ō' => 'O',
			'Ŗ' => 'R', 'Š' => 'S', 'Ū' => 'U', 'Ž' => 'Z',

			// lithuanian
			'ą' => 'a', 'ę' => 'e', 'ė' => 'e', 'į' => 'i', 'ų' => 'u', 'Ą' => 'A', 'Ę' => 'E', 'Ė' => 'E',
			'Į' => 'I', 'Ų' => 'U',
		);

		if (isset($cache[$str]))
		{
			return $cache[$str];
		}

		if (is_null($replaceSpecialChars))
		{
			//$replaceSpecialChars = ActiveRecordModel::getApplication()->getConfig()->get('URL_REPLACE_SPECIAL_CHARS');
		}

		if ($replaceSpecialChars)
		{
			$str = strtr($str, $table);
		}

		// optimized for performance
		return $cache[$str] = preg_replace('/ {1,}/', '-', trim(strtr($str, '$&+\/:;=?@."\'#*><-,%', '                        ')));
	}
}
