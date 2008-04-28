<div class="userItem">

{include file="user/layout.tpl"}

<div id="content" class="left right">

	<h1>{t _your_files}: {$item.Product.name_lang}</h1>

	{include file="user/userMenu.tpl" current="homeMenu"}

	<div id="userContent">

	{if $files}
		{foreach from=$files item="item"}
			<h3>
				<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
			</h3>
			{include file="user/fileList.tpl" item=$item}
		{/foreach}
	{/if}

	</div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>