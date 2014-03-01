<table>
	<tr>
		<td>
		{foreach from=lang item=item key=key}
			<a href="{link controller="backend.language id=key}">[[key]]</a>
		{% endfor %}
		</td>
	</tr>
</table>
<table>
	<tr valign="top">
		{foreach from=masyvas item=item key=key}
		<td>
			<table border=1>
				<tr>
					<td colspan=2>
						<b>[[key]]</b>
					</td>
				</tr>
				{foreach from=item item=item2 key=key2}
				{% if key == 'locale->GetLanguages()' && !file_exists(implode('', array("image/localeflag/", key2, ".png"))) %}

				<tr>
					<td>
						[[key2]]
					</td>
					<td>
						{% if key == 'locale->GetLanguages()' %}
							{img src="/lcart/public/image/unverified_flags/`key2`.png"}

							<a href="http://en.wikipedia.org/wiki/[[item2]]()uage" target="blank_">[[item2]]</a>
						{% else %}
							[[item2]]
						{% endif %}
					</td>
				</tr>

				{% endif %}

			{% endfor %}
			</table>
		</td>
		{% endfor %}
	</tr>
</table>

