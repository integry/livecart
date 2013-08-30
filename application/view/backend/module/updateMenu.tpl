<div class="updateMenu">
	{form handle=$form}
		{*
		{% if !$repositories %}
			<div class="noRepositories errorText moduleError">
				<span>{tip _no_repositories _tip_custom_module}</span>
			</div>
		{% endif %}
		*}

		{% if $lines %}
			[[ selectfld('channel', '_channel', lines) ]]
		{% endif %}

		{% if $versions %}
			[[ selectfld('version', '_version', versions) ]]
		{% endif %}

		{% if !$versions %}
			<div class="noVersions errorText moduleError">
				<span>{tip _no_versions _tip_no_versions}</span>
			</div>
		{% endif %}

		<input type="submit" class="submit" value="{tn _proceed_update}" />
		{t _or}
		<a href="#cancel" class="cancel">{t _cancel}</a>
	{/form}
</div>