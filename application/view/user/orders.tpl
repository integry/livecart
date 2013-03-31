{pageTitle}{t _your_orders}{/pageTitle}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="orderMenu"}
{include file="block/content-start.tpl"}

	<div class="resultStats">
		{if $orders}
			{if $count > $perPage}
				{maketext text=_displaying_orders params="`$from`,`$to`,`$count`"}
			{else}
				{maketext text=_orders_found params=$count}
			{/if}
		{else}
			{t _no_orders_found}
		{/if}
	</div>

	{foreach from=$orders item="order"}
		{include file="user/orderEntry.tpl" order=$order}
	{/foreach}

	{if $count > $perPage}
		{capture assign="url"}{link controller=user action=orders id=0}{/capture}
		<div class="resultPages">
			Pages: {paginate current=$currentPage count=$count perPage=$perPage url=$url}
		</div>
	{/if}

{include file="block/content-stop.tpl"}

{include file="layout/frontend/footer.tpl"}

</div>