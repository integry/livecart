{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">

    <h1>{t _edit_billing_address}</h1>

	{include file="user/userMenu.tpl" current="addressMenu"}
    
    {form action="controller=user action=saveShippingAddress id=`$addressType.ID`" handle=$form}
        {include file="user/addressForm.tpl"}                        
        <input type="submit" class="submit" value="{tn _continue}" />        
    {/form}

</div>

{include file="layout/frontend/footer.tpl"}    