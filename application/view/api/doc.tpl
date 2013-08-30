<h1>{t _livecart_api}</h1>

<p>
	{t _api_testing_info}
</p>

<h2>{t _api_sections}</h2>

<ul>
	{foreach from=$classes key=class item=params}
		<li><a href="[[ url("api/docview", "class=`$class`") ]]">[[params.path]]</a></li>
	{/foreach}
</ul>

<h2>{t _api_auth}</h2>
{% if !empty(authMethods) %}
	<ul>
		{foreach from=$authMethods item=class}
			<li><a href="[[ url("api/docauth", "class=`$class`") ]]">[[ t(class) ]]</a></li>
		{/foreach}
	</ul>
{% else %}
	<p>{t _no_auth_methods}</p>
{% endif %}
