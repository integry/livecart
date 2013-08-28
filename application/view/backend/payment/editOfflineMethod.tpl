<span>
	<a href="javascript:void(0)" onclick="Backend.Payment.OfflinePaymentMethodEditor.toggleEditMode(this);"><img src="image/silk/pencil.png" border="0" /></a>
	<span>[[name]]</span>
	<span class="progressIndicator" style="display: none;"></span>
	<select class="hidden"
		onchange="Backend.Payment.OfflinePaymentMethodEditor.changed(this);"
		onblur="Backend.Payment.OfflinePaymentMethodEditor.toggleViewMode(this);">
		{foreach from=$methods item="method"}
			<option></option>
			<option value="[[method.ID]]"{if $handlerID == $method.ID} selected="selected"{/if}>[[method.name]]</option>
		{/foreach}
	</select>
	<input type="hidden" name="url" value="{link controller="backend.payment" action=changeOfflinePaymentMethod id=$ID}" />
</span>