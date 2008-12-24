{*nolive*}
<div id="customizeMenu">
	<span id="modeTitle">Live Customization Mode</span>
	<ul>
		<li id="modeTemplate" {if 'template' == $mode}class="active"{/if}><a href="{link controller=backend.customize action=mode query="mode=template" returnPath=true}">Templates</a></li>
		<li id="modeCss" {if 'css' == $mode}class="active"{/if}><a  href="{link controller=backend.customize action=mode query="mode=css" returnPath=true}">CSS</a></li>
		<input type="button" class="button" id="cssSave" value="Save CSS" />
		<li id="modeTranslation" {if 'translate' == $mode}class="active"{/if}><a href="{link controller=backend.customize action=mode query="mode=translate" returnPath=true}">Translations</a></li>
		<li id="modeExit"><a href="{link controller=backend.customize action=mode query="mode=exit" returnPath=true}">Exit</a></li>
	</ul>
</div>

{if 'translate' == $mode}
	<div id="transDialogBox" style="position: absolute;z-index: 10000; display: none;">
		<div class="menuLoadIndicator" id="transDialogIndicator"></div>
		<div id="transDialogContent">
		</div>
	</div>

	<div id="transDialogMenu" style="display:none;position: absolute;z-index: 60000; background-color: yellow; border: 1px solid black; padding: 3px;"><a href="#" id="transLink">{tn _live_translate}</a></div>

	<script type="text/javascript">
		var cust = new Customize();
		cust.setActionUrl('{link controller=backend.language action=translationDialog}');
		cust.initLang();
		new Draggable('transDialogBox');
		Event.observe('transDialogBox', 'mousedown', cust.stopTransCancel.bind(cust), false);
		Event.observe('transLink', 'click', cust.translationMenuClick.bindAsEventListener(cust), true);
	</script>
{/if}

{if 'css' == $mode}
	<script type="text/javascript">
		Backend.Router.setUrlTemplate('{link controller=controller action=action}');
		new CssCustomize({json array=$theme});
	</script>
{/if}