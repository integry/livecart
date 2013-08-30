<li id="[[module.path]]" class="module module_{$module.path|replace:'.':'_'} activeList_odd {% if $module.newest && ($module.newest.version != $module.Module.version) %}needUpdate{% endif %} {% if !$module.isEnabled %}disabled{% endif %} {% if $module.isInstalled %}installed{% endif %}">
	<div>
		<span class="moduleStatus">
			<input type="checkbox" class="checkbox" {% if $module.isEnabled %}checked="checked"{% endif %} {% if !$module.isInstalled %}disabled="disabled"{% endif %} />
			<span class="progressIndicator" style="display: none;"></span>
		</span>

		<div class="moduleContent">

			{% if $module.newest && ($module.newest.version != $module.Module.version) %}
				<span class="updateInfo">
					{t _newest_version}: <span class="newestVersionNumber">[[module.newest.version]]</span>
					<span class="updateTime">[[module.newest.time.date_medium]]</span>
				</span>
			{% endif %}

			<span class="moduleName">[[module.Module.name]]</span>
			{% if !$module.isEnabled %}
				<span class="moduleInactive">({t _inactive})</span>
			{% endif %}

			<div class="moduleVersion">
				{t _version}: [[module.Module.version]] | {t _channel}: <span class="channel channel-[[module.Module.line]]">[[module.Module.line]]</span>
			</div>

			{% if !$module.isInstalled %}
				<div class="moduleInstallationStatus"><span class="installed_no">{t _not_installed}</span> (<a class="installAction" href="#install">{t _install}</a>)</div>
			{% else %}
				<div class="moduleUpdate">
						{% if $module.repo %}
							<a class="updateAction" href="#update">Update or downgrade</a> |
						{% endif %}

						<a class="installAction" href="#deinstall">{t _deinstall}</a>
					<br />
				</div>
			{% endif %}

			<div class="updateMenuContainer"></div>
		</div>

	</div>
</li>

<script type="text/javascript">
	$('[[module.path]]').repo = {ldelim} repo: {json array=$module.repo.repo}, handshake: {json array=$module.repo.handshake} {rdelim};
	$('[[module.path]]').version = {json array=$module.Module.version};
</script>