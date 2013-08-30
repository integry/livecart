{pageTitle help="update"}[[ branding({t _update_livecart}) ]]{/pageTitle}

{includeCss file="backend/Update.css"}

[[ partial("layout/backend/header.tpl") ]]

<table id="versionCompare">
	<tr>
		<td>{t _newest}:</td>
		<td class="version">[[newest]]</td>
	</tr>
	<tr>
		<td>{t _current}:</td>
		<td class="version {% if !empty(needUpdate) %}outdated{% else %}upToDate{% endif %}">[[current]]</td>
	</tr>
</table>

<p>
{% if !empty(needUpdate) %}
	[[ branding({t _newer_available}) ]].
{% else %}
	[[ branding({t _up_to_date}) ]]
{% endif %}
</p>

[[ partial("layout/backend/footer.tpl") ]]