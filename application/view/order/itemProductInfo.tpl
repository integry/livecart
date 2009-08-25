{if $item.Product.isDownloadable && $downloadLinks}
	<a href="{link controller=user action=item id=$item.ID}">{$item.Product.name_lang}</a>
{else}
	<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
{/if}

{if $item.Product.variations}
	<span class="variations">
		(&rlm;{include file="order/itemVariationsList.tpl"})
	</span>
{/if}

{include file="user/itemOptions.tpl" options=$item.options}

{sect}
	{header}
		<ul class="subItemList">
	{/header}
	{content}
		{foreach $item.subItems as $subItem}
			{if $subItem.Product.isDownloadable}
				<li>
					<a href="{link controller=user action=item id=$subItem.ID}">{$subItem.Product.name_lang}</a>
					{include file="user/itemOptions.tpl" options=$subItem.options}
				</li>
			{/if}
		{/foreach}
	{/content}
	{footer}
		</ul>
	{/footer}
{/sect}