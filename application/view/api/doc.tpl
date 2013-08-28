<h1>{t _livecart_api}</h1>

<p>
	{t _api_testing_info}
</p>

<h2>{t _api_sections}</h2>

<ul>
	{foreach from=$classes key=class item=params}
		<li><a href="{link controller=api action=docview query="class=`$class`"}">[[params.path]]</a></li>
	{/foreach}
</ul>

<h2>{t _api_auth}</h2>
{if $authMethods}
	<ul>
		{foreach from=$authMethods item=class}
			<li><a href="{link controller=api action=docauth query="class=`$class`"}">{translate text=$class}</a></li>
		{/foreach}
	</ul>
{else}
	<p>{t _no_auth_methods}</p>
{/if}
