<?php

class MyLocale_Maketext_ru extends MyLocale_Maketext{
  
  	function quant($args)
    {
        $num   = $args[0];
        $forms = array_slice($args,1);

        $_return = "$num ";
    
    	if ($num % 100 > 10 && $num % 100 < 20) {

		  	$_return .= $forms[2];		  	
		} else if ($num % 10 == 1) {
		  
		  	$_return .= $forms[0];	

		} else if ($num % 10 == 0) {
		  
		  	$_return .= $forms[2];	
		} else if ($num % 10 < 5){
		  
		  	$_return .= $forms[1];
		} else {
		  
		  	$_return .= $forms[2];
		} 
 
        return $_return;
    }    	
} 

?>