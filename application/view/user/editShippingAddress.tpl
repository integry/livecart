{loadJs form=true}

<div class="userEditShippingAddress">

{include file="user/layout.tpl"}

{include file="user/userMenu.tpl" current="addressMenu"}
<div id="content">

	<h1>{t _edit_shipping_address}</h1>

	{form action="controller=user action=saveShippingAddress id=`$addressType.ID`" handle=$form}
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

	</div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>