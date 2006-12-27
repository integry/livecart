<?php

function smarty_function_baseUrl($params, $smarty)
{
	return Router::getBaseUrl();
}

?>