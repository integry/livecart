{form handle=$form action="controller=backend.customerOrder action=saveQuickEdit id=`$order.ID`"  onsubmit="return false;" method="post"}
	<fieldset class="quickEditOuterContainer">
		<div class="quickEditContainerOrder">
			{include file="backend/customerOrder/block/orderInfo.tpl" order=$order}
		</div>
		{include file="block/activeGrid/quickEditControls.tpl" nosave=true}
	</fieldset>
{/form}