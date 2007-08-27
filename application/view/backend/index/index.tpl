{pageTitle help=""}{t LiveCart Backend}{/pageTitle}

{includeCss file="backend/Index.css"}

{include file="layout/backend/header.tpl"}

<fieldset class="stats">
    <legend>{t Order Overview}</legend>
    <form>
        <p>
            <label>
            <select name="period" style="width: 130px; font-size: smaller; margin: 0;">
                <option value="-24 hours | now">Last 24 hours</option>
                <option value="-3 days | now">Last 3 days</option>
                <option value="Monday | now">This week</option>
                <option value="last Monday | last Sunday">Last week</option>
                <option value="0 this month | now">This month</option>
                <option value="0 last month | now">Last month</option>
                <option value="January 1st | now">This year</option>
                <option value="January 1st last year | December 31 last year">Last year</option>
            </select>:</label>
            <label>{$orderCount.last}</label>        
        </p>
    
        <p>
            <label>New:</label>
            <label>{$orderCount.new}</label>        
        </p>
    
        <p>
            <label>Processing:</label>
            <label>{$orderCount.processing}</label>        
        </p>
    
        <p>
            <label>Unread messages:</label>
            <label>{$orderCount.messages}</label>        
        </p>
    
    </form>
</fieldset>

<fieldset class="stats">
    <legend>{t Inventory}</legend>
    
    <form>
        <p>
            <label>Low on stock:</label>
            <label>{$inventoryCount.lowStock}</label>        
        </p>
    
        <p>
            <label>Out of stock:</label>
            <label>{$inventoryCount.outOfStock}</label>
        </p>
    </form>
</fieldset>

<fieldset class="stats">
    <legend>{t Overall}</legend>
    
    <form>
        <p>
            <label>Active products:</label>
            <label>{$rootCat.availableProductCount}</label>        
        </p>
    
        <p>
            <label>Inactive products:</label>
            <label>{$rootCat.unavailableProductCount}</label>
        </p>

        <p>
            <label>Orders:</label>
            <label>{$orderCount.total}</label>
        </p>

    </form>
</fieldset>

{include file="layout/backend/footer.tpl"}