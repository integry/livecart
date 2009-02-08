{*nolive*}
<div id="customizeMenu" onmouseover="Event.stop(event);">
	<div id="customizeMenuInner">
		<span id="modeTitle">{t _cust_mode}</span>
		<ul>
			<li id="modeTemplate" {if 'template' == $mode}class="active"{/if}><a href="{link controller=backend.customize action=mode query="mode=template" returnPath=true}">{t _templates}</a></li>
			<li id="modeCss" {if 'css' == $mode}class="active"{/if}><a  href="{link controller=backend.customize action=mode query="mode=css" returnPath=true}">{t _css}</a></li>
			{if 'css' == $mode}
				<input type="button" class="button" id="cssNewRule" value="{t _css_add}" />
				<input type="button" class="button" id="cssSave" value="{t _css_save}" />
				<span class="progressIndicator" id="cssSaveIndicator" style="display: none;"></span>
				<div id="newRuleMenu" style="display: none;">
					<form>
						<p>
							<label class="wide">{t _css_rule_sel}:</label>
							<input type="text" class="text wide" id="cssNewRuleName" />
							<span class="cssExample">{t _css_example}: <strong>.product-index h1</strong></span>
							<span class="errorText hidden"></span>
						</p>
						<p>
							<label class="wide">{t _css_rule_text}:</label>
							<textarea id="cssNewRuleText"></textarea>
							<span class="cssExample">{t _css_example}: <strong>color: green</strong></span>
							<span class="errorText hidden"></span>
						</p>
						<p>
							<input type="button" class="button" id="cssNewRuleSave" value="{tn _css_add_rule}" />
							{t _or}
							<a class="cancel" href="#" id="cssNewRuleCancel">{t _cancel}</a>
						</p>
					</form>
				</div>
			{/if}
			<li id="modeTranslation" {if 'translate' == $mode}class="active"{/if}><a href="{link controller=backend.customize action=mode query="mode=translate" returnPath=true}">{t _translations}</a></li>
			<li id="modeExit"><a href="{link controller=backend.customize action=mode query="mode=exit" returnPath=true}">{t _exit}</a></li>
		</ul>
		<div id="customizeMsg"><div style="display: none;"></div></div>
	</div>
</div>

{if 'css' == $mode}
	<script type="text/javascript">
		Backend.Router.setUrlTemplate('{link controller=controller action=action}');
		var cust = new CssCustomize({json array=$theme});
		cust.errSelectorMsg = '{t _css_err_selector|escape}';
		cust.errTextMsg = '{t _css_err_text|escape}';
		cust.ruleAddedMsg = '{t _css_rule_added|escape}';
		cust.savedMsg = '{t _css_saved|escape}';
		cust.firebugMsg = '{maketext text="_css_firebug" params='<a href="http://getfirebug.com" target="_blank">Firebug</a>'}';

		cust.showMessage(cust.firebugMsg, true);
	</script>
{/if}