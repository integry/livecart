<?php

ClassLoader::import("application.model.datasync.ModelApi");

class ProductApi extends ModelApi
{
	public static function canParse(Request $request)
	{
		return false;
	}
}
?>
