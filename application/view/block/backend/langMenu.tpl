 | <a href="#" onClick="{literal}if (showLangMenu) { showLangMenu(true); }; return false;"{/literal}>{t _change_language}</a>

<div id="langMenuContainer">
	<div id="langMenuIndicator" class="menuLoadIndicator"></div>
</div>

<script type="text/javascript">
	var langMenuUrl = '{link controller=backend.language action=langSwitchMenu query="returnRoute=$returnRoute"}';
</script>