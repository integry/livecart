<div class="updateMenu">
	{form handle=$form}
		{*
		{if !$repositories}
			<div class="noRepositories errorText moduleError">
				<span>{tip _no_repositories _tip_custom_module}</span>
			</div>
		{/if}
		*}

		{if $lines}
			<p>
				<label>{t _channel}:</label>
				{selectfield name="channel" options=$lines}
			</p>
		{/if}

		{if $versions}
			<p>
				<label>{t _version}:</label>
				{selectfield name="version" options=$versions}
			</p>
		{/if}

		{if !$versions}
			<div class="noVersions errorText moduleError">
				<span>{tip _no_versions _tip_no_versions}</span>
			</div>
		{/if}

		<input type="submit" class="submit" value="{tn _proceed_update}" />
		{t _or}
		<a href="#cancel" class="cancel">{t _cancel}</a>
	{/form}
</div>