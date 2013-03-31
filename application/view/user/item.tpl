{pageTitle}{t _your_files}: {$item.Product.name_lang}{/pageTitle}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="homeMenu"}
{include file="block/content-start.tpl"}

	{if $files}
		{foreach from=$files item="item"}
			<h3>
				<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
			</h3>
			{include file="user/fileList.tpl" item=$item}
		{/foreach}
	{/if}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}