<h1><a href="{link controller=api action=doc}">{t _livecart_api}</a> &gt; {$info.path}</h1>

<h2>{t _api_actions}</h2>

<ul>
	{foreach from=$info.actions item=action}
		<li>{$info.path}.<strong><a href="{link controller=api action=docaction query="class=`$className`&actn=`$action`"}">{$action}</a></strong></li>
	{/foreach}
</ul>
