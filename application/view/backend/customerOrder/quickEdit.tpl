{form handle=$form action="controller=backend.customerOrder action=saveQuickEdit id=`$order.ID`"  onsubmit="return false;" method="post"}
	<fieldset>
		<legend>{t _quick_edit}</legend>
		<div class="quickEditContainerOrder">
			{include file="backend/customerOrder/block/orderInfo.tpl" order=$order}
		</div>
	</fieldset>
	{include file="block/activeGrid/quickEditControls.tpl" nosave=true}
{/form}