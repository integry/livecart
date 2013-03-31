{loadJs form=true}
{pageTitle}{t _add_shipping_address}{/pageTitle}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="addressMenu"}
{include file="block/content-start.tpl"}

	{form action="controller=user action=doAddShippingAddress" handle=$form}
		{include file="user/addressForm.tpl"}
		<p>
			<label></label>
			<input type="submit" class="submit" value="{tn _continue}" />
			<label class="cancel">
				{t _or}
				<a class="cancel" href="{link route=$return}">{t _cancel}</a>
			</label>
		</p>
	{/form}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}