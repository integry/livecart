<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />	

    <title>
        LiveCart Installer - {$PAGE_TITLE}
    </title>
	<base href="{baseUrl}" />
	
	<!-- Css includes -->
	<link href="stylesheet/install/Install.css" rel="Stylesheet" type="text/css"/>
	<!--[if IE]>
		<link href="stylesheet/frontend/InstallIE.css" rel="Stylesheet" type="text/css"/>
	<![endif]-->
	
    {includeJs file="library/prototype/prototype.js"}
    {includeJs file="library/livecart.js"}
    {includeJs file="library/form/Validator.js"}
    {includeJs file="library/form/ActiveForm.js"}
    {includeJs file="library/scriptaculous/effects.js"}    
    {compiledJs glue=false}
	
</head>

<body>
	<div id="container" class="lang_{localeCode}">
		<div id="header">
			LiveCart Installer			
		</div>
		<div id="installContent">
			{$ACTION_VIEW}
		</div>
	</div>	
</body>

</html>