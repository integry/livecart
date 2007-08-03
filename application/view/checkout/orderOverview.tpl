<table class="table shipment" id="payItems">            
    <thead>
        <tr>
            <th class="productName">Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>                            
    </thead>
    <tbody>

    {foreach from=$order.shipments key="key" item="shipment"}
        {include file="order/orderTableDetails.tpl"}
    {/foreach}  
  
    {foreach from=$order.taxes.$currency item="tax"}
        <tr>                    
            <td colspan="3" class="tax">{$tax.name_lang}:</td>
            <td>{$tax.formattedAmount}</td>
        </tr>
    {/foreach}        
      
    <tr>
        <td colspan="3" class="subTotalCaption">{t _total}:</td>
        <td class="subTotal">{$order.formattedTotal.$currency}</td>                        
    </tr>

    </tbody>        
</table>    
