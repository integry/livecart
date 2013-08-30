<a href="#" id="langSwitchLink" onClick="if (showLangMenu) { showLangMenu(true); }; return false;" {% if $currentLang.image %}class="hasFlag" style="background-image: url([[currentLang.image]])"{% endif %}>{t _change_language}</a> |

<div id="langMenuContainer">
	<div id="langMenuIndicator" class="menuLoadIndicator"></div>
</div>

<script type="text/javascript">
	var langMenuUrl = '{link controller="backend.language" action=langSwitchMenu query="returnRoute=$returnRoute"}';
</script>