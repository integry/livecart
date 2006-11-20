{literal}
<script type="text/javascript">
	function showLangMenu(display) {		
		menu = document.getElementById('langMenuContainer');
		if (display)
		{
			menu.style.display = 'block';
			new Ajax.Updater('langMenuContainer', '{/literal}{link controller=backend.language action=langSwitchMenu query="returnRoute=$returnRoute"}{literal}');
		}
		else
		{
		  	menu.style.display = 'none';
		}
	}
</script>
{/literal}

<div id="langMenuContainer">
	<div id="langMenuIndicator">
		<img src="image/indicator.gif">
	</div>
</div>