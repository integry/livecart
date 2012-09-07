{assign var="fields" value='USER_FIELDS'|config}

{block FORM-NEW-CUSTOMER-TOP}

{include file="user/block/nameFields.tpl"}

{input name="email"}
	{label}{t _your_email}:{/label}
	{textfield}
{/input}

{input name="newsletter"}
	{checkbox}
	{label}{t _newsletter_signup}{/label}
{/input}

{include file="user/block/phoneField.tpl"}

{if 'PASSWORD_GENERATION'|config != 'PASSWORD_AUTO'}
	{include file="user/block/passwordFields.tpl"}
{/if}

{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}
{include file="block/eav/fields.tpl" eavPrefix=$prefix}

{if $showHeading && $order.isShippingRequired && !'REQUIRE_SAME_ADDRESS'|config}
	<h3>{t _billing_address}</h3>
{/if}

{include file="user/block/addressFields.tpl"}
