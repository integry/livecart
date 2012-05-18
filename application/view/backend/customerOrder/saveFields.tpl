<fieldset>
	<legend>{t _custom_info}</legend>

	<ul class="menu orderFields_showEdit">
		<li class="order_editFields">
			<a href="#edit" {denied role='order.update'}style="display: none"{/denied}>{t _edit}</a>
		</li>
		<li class="done order_cancelEditFields">
			<a href="#cancel">{t _cancel}</a>
		</li>
	</ul>
	<div class="clear"></div>

	<div class="overview">
		{include file="backend/eav/view.tpl" item=$order}
		{if !$order.attributes}
			<p>{t _no_info_entered_yet}</p>
		{/if}
	</div>

	{form handle=$fieldsForm action="controller=backend.customerOrder action=saveFields" method="POST"}
		{include file="backend/eav/fields.tpl" item=$order}
		<input type="hidden" name="id" value="{$order.ID}" />
		<fieldset class="controls">
			<span style="display: none;" class="progressIndicator"></span>
			<input type="submit" class="button submit" value="{t _save}" />
			{t _or}
			<a href="#cancel" class="cancel">{t _cancel}</a>
		</fieldset>
	{/form}

</fieldset>
{literal}<script type="text/javascript">{/literal}
	new Backend.CustomerOrder.CustomFields({$order.ID});
</script>