{loadJs form=true}

<div class="userEditBillingAddress">

{include file="user/layout.tpl"}

{include file="user/userMenu.tpl" current="addressMenu"}
<div id="userContent">

<div id="content" class="left right">

	<h1>{t _edit_billing_address}</h1>

		<fieldset class="container">

		{form action="controller=user action=saveBillingAddress id=`$addressType.ID`" handle=$form}
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

		</fieldset>

	</div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>