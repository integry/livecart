<h3>
    <a href="{link controller=user action=viewOrder id=$order.ID}">{$order.formatted_dateCompleted.date_long}</a>    
</h3>

{if $order.unreadMessageCount}
    <p class="messages">
        <a href="{link controller=user action=viewOrder id=$order.ID}#msg" class="messages">
            {maketext text="[quant,_1,unread message,unread messages]" params=$order.unreadMessageCount}
        </a>
    </p>
{/if}

<div class="orderStatus">
    {t _status}: 
    {include file="user/orderStatus.tpl" order=$order}
</div>

<div class="orderDetails">

   <div class="orderMenu">
   
       <ul>
           <li><a href="{link controller=user action=viewOrder id=$order.ID}" class="viewOrder">{t _view_details}</a></li>
           {if !$order.isCancelled}
           <li><a href="{link controller=user action=orderInvoice id=$order.ID}" class="invoice">{t _order_invoice}</a></li>
           {/if}
       </ul>
       
       <div>
           {t _order_id}: {$order.ID}
       </div>
       
       {if $order.ShippingAddress}
           <div>
               {t _recipient}: {$order.ShippingAddress.fullName}
           </div>
       {/if}

       <div class="orderTotal">
           {t _total}: <strong>{$order.formattedTotal[$order.Currency.ID]}</strong>
       </div>
   
   </div>

   <div class="orderContent">
    
        <ul>
        {foreach from=$order.cartItems item="item"}
	        <li>{$item.count} x 
                {if $item.Product.isDownloadable}
                    <a href="{link controller=user action=item id=$item.ID}">{$item.Product.name_lang}</a>
                {else}
                    {$item.Product.name_lang}
                {/if}
            </li>
	    {/foreach}
	    </ul>	    
    </div>
    
</div>