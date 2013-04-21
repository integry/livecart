{loadJs form=true}
{pageTitle}{t _edit_shipping_address}{/pageTitle}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="addressMenu"}
{include file="block/content-start.tpl"}

	{form action="controller=user action=saveShippingAddress id=`$addressType.ID`" handle=$form}
		{include file="user/addressForm.tpl"}

		{include file="block/submit.tpl" caption="_continue" cancelRoute=$return}

	{/form}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}