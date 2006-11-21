{literal}
<script type="text/javascript">
	function showLangMenu(display) {		
		menu = document.getElementById('langMenuContainer');
		if (display)
		{
			menu.style.display = 'block';
			new Ajax.Updater('langMenuContainer', '{/literal}{link controller=backend.language action=langSwitchMenu query="returnRoute=$returnRoute"}{literal}');
			
			try 
			{
				// Mozilla
				document.addEventListener('click', hideLangMenu, true);
			} 
			catch (e)
			{
			  	// IE...
				setTimeout(ieHideLangMenu, 500);
			}
		}
		else
		{
		  	menu.style.display = 'none';

			try 
			{
				// Mozilla
				document.removeEventListener('click', hideLangMenu, false);
			} 
			catch (e)
			{
			  	// IE...
				document.detachEvent('onclick', hideLangMenu); 	 	
			}
		}
	}

	function ieHideLangMenu()
	{
		document.attachEvent('onclick', hideLangMenu); 	 	
	}

	function hideLangMenu()
	{
		showLangMenu(false);
	}
</script>
{/literal}

<div id="langMenuContainer">
	<div id="langMenuIndicator">
		<img src="image/indicator.gif">
	</div>
</div>