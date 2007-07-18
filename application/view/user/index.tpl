<div class="userIndex">

{include file="layout/frontend/header.tpl"}

<div id="content" class="left right">
	
	<h1>{t _your_account} ({$user.fullName})</h1>
	
	{if $userConfirm}
	<div class="confirmationMsg">
        <div>{$userConfirm}</div>
	</div>
	{/if}
	
	{include file="user/userMenu.tpl" current="homeMenu"}

    <div id="userContent">

    {if $files}
        <h2>{t Download Recently Purchased Files}</h2>    
        
        {foreach from=$files item="item"}        
            <h3>
                <a href="{link controller=user action=item id=$item.ID}">{$item.Product.name_lang}</a>
            </h3>
            {include file="user/fileList.tpl" item=$item}
        {/foreach}            
    {/if}

    {if $orders}
        <h2>{t Your Recent Orders}</h2>
        {foreach from=$orders item="order"}    
    	    {include file="user/orderEntry.tpl" order=$order}
        {/foreach}
    {/if}
    
    </div>
    
</div>

{include file="layout/frontend/footer.tpl"}

</div>