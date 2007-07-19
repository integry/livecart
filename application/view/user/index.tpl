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
    
    <fieldset class="container">

        {if $notes}    	
            <h2>{t Unread Messages}</h2>    
           <ul class="notes">
        	   {foreach from=$notes item=note}
        	       <a href="{link controller=user action=viewOrder id=`$note.orderID`}#msg">{t _order} #{$note.orderID}</a>
                   {include file="user/orderNote.tpl" note=$note}
        	   {/foreach}
    	   </ul>
    	{/if}
    
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
    
    </fieldset>
    
    </div>
    
</div>

{include file="layout/frontend/footer.tpl"}

</div>