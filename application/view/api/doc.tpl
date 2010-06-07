<h1>{t _livecart_api}</h1>

<p>
	{t _api_testing_info}
</p>

<h2>{t _api_sections}</h2>

<ul>
	{foreach from=$classes key=class item=params}
		<li><a href="{link controller=api action=docview query="class=`$class`"}">{$params.path}</a></li>
	{/foreach}
</ul>
