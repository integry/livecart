{capture assign="after"}
	<option value="">{t _add_new_address}</option>
{/capture}

{selectfield name="address_`item.ID`" class="multiAddress" options=addresses after=after onchange="if (!this.value) window.location.href='{link controller=user action=addShippingAddress returnPath=true}'"}