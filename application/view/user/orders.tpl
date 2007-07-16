<div class="userIndex">

{include file="layout/frontend/header.tpl"}

<div id="content" class="left right">
	
	<h1>{t _your_orders}</h1>
	
	{include file="user/userMenu.tpl" current="homeMenu"}

    <div id="userContent">

        {if $orders}
            {foreach from=$orders item="order"}    
        	    {include file="user/orderEntry.tpl" order=$order}
            {/foreach}
        {else}
            {t _no_orders_found}
        {/if}
   
        {if $count > $perPage}
        	{capture assign="url"}{link controller=user action=orders id=0}{/capture}
            <div class="resultPages">
        		Pages: {paginate current=$currentPage count=$count perPage=$perPage url=$url}
        	</div>
        {/if}   
    
    </div>
    
</div>

{include file="layout/frontend/footer.tpl"}

</div>