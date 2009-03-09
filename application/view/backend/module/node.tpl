<li id="{$module.path}" class="module activeList_odd {if !$module.isEnabled}disabled{/if} {if $module.isInstalled}installed{/if}">
	<div>
		<span class="moduleStatus">
			<input type="checkbox" class="checkbox" {if $module.isEnabled}checked="checked"{/if} {if !$module.isInstalled}disabled="disabled"{/if} />
			<span class="progressIndicator" style="display: none;"></span>
		</span>

		<div class="moduleContent">
			<span class="moduleName">{$module.Module.name}</span>

			{if !$module.isEnabled}
				<span class="moduleInactive">({t _inactive})</span>
			{/if}

			<div class="moduleInstallationStatus">{t _installed}: {if $module.isInstalled}<span class="installed_yes">{t _yes}</span> (<a class="installAction" href="">{t _deinstall}</a>){else}<span class="installed_no">{t _no}</span> (<a class="installAction" href="">{t _install}</a>){/if}</div>
		</div>

	</div>
</li>
