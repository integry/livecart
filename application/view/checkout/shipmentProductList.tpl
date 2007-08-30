<table class="table shipment">            
    <thead>
        <tr>
            <th class="productName">Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>                            
    </thead>
    <tbody>
        {foreach from=$shipment.items item="item" name="shipment"}
            <tr{zebra loop="shipment"}>                    
                <td class="productName"><a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a></td>
                <td>{$item.Product.formattedPrice.$currency}</td>
                <td>{$item.count}</td>
                <td>{$item.formattedSubTotal}</td>
            </tr>
        {/foreach}            
    
        {foreach from=$shipment.taxes item="tax"}
            <tr>                    
                <td colspan="3" class="tax">{$tax.TaxRate.Tax.name_lang}:</td>
                <td>{$tax.formattedAmount.$currency}</td>
            </tr>
        {/foreach}            

        <tr>
            <td colspan="3" class="subTotalCaption">{t _subtotal}:</td>
            <td class="subTotal">{$shipment.formattedSubTotal.$currency}</td>                        
        </tr>
    </tbody>        
</table>    
