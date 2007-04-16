{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">

    <h1>{t _add_shipping_address}</h1>
    
    {form action="controller=user action=doAddShippingAddress" handle=$form}
        {include file="user/addressForm.tpl"}                        
        <input type="submit" class="submit" value="{tn _continue}" />        
    {/form}

</div>

{include file="layout/frontend/footer.tpl"}    