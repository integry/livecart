<fieldset class="container" {denied role="product.create,product.update"}style="display: none"{/denied}>
	<ul class="menu rpp_new_menu">
		<li class="addRpp"><a href="#new_rpp" id="rpp_new_show_[[product.ID]]" class="rpp_new_show">{t _add_new_recurring_product_period}</a></li>
		<li class="done addRppCancel" style="display: none"><a href="#cancel_rpp" id="rpp_new_cancel" class="rpp_new_cancel">{t _cancel_adding_new_recurring_product_period}</a></li>
	</ul>
</fieldset>

<fieldset style="display: none;" class="addForm rpp_new_form" id="rpp_new_form_[[product.ID]]">
	<legend>{t _add_new_recurring_product_period|capitalize}</legend>
	[[ partial('backend/recurringProductPeriod/form.tpl', ['recurringProductPeriod': $newRecurringProductPeriod, 'form': $newForm]) ]]
</fieldset>

<ul class="activeList activeList_add_delete activeList_add_edit" id="recurringProductPeriods_[[product.ID]]">
	{foreach from=$recurringProductPeriods item="recurringProductPeriod"}
		<li id="recurringProductPeriod_[[recurringProductPeriod.productID]]_[[recurringProductPeriod.ID]]">
			<span class="error">{$recurringProductPeriod.name_lang|escape}</span>
		</li>
	{/foreach}
</ul>

<script type="text/javascript">
	ActiveList.prototype.getInstance("recurringProductPeriods_[[product.ID]]",
		Backend.RecurringProductPeriod.prototype.ActiveListCallbacks);

	Backend.RecurringProductPeriod.prototype.properties = {literal}{{/literal}
		// link_update: "{link controller="backend.recurringProductPeriod" action=update}",
		// link_create: "{link controller="backend.recurringProductPeriod" action=create}",
		link_edit: "{link controller="backend.recurringProductPeriod" action=edit id=_id_}",
		link_remove: "{link controller="backend.recurringProductPeriod" action=delete id=_id_}",
		// link_sort: "{link controller="backend.recurringProductPeriod" action=sort}",
		message_confirm_remove: "{t _confirm_removing_rpp}"
	{literal}}{/literal};

	Event.observe($("rpp_new_show_[[product.ID]]"), "click", function(e)
	{literal}{{/literal}
		e.preventDefault();
		var newForm = Backend.RecurringProductPeriod.prototype.getInstance( $("rpp_new_form_[[product.ID]]").down('form') );
		newForm.showNewForm();
	{literal}}{/literal});
</script>