<h1><a href="[[ url("api/doc") ]]">{t _livecart_api}</a> &gt; [[info.path]]</h1>

<h2>{t _api_actions}</h2>

<ul>
	{foreach from=$info.actions item=action}
		<li>[[info.path]].<strong><a href="[[ url("api/docaction", "class=`$className`&actn=`$action`") ]]">[[action]]</a></strong></li>
	{/foreach}
</ul>
