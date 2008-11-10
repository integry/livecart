<div class="userItem">

{include file="user/layout.tpl"}

{include file="user/userMenu.tpl" current="homeMenu"}
<div id="content">

	<h1>{t _your_files}: {$item.Product.name_lang}</h1>

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