<fieldset class="container" {denied role="product.create,product.update"}style="display: none"{/denied}>
	<ul class="menu rpp_new_menu">
		<li class="addRpp"><a href="#new_rpp" id="rpp_new_show_[[product.ID]]" class="rpp_new_show">{t _add_new_recurring_product_period}</a></li>
		<li class="done addRppCancel" style="display: none"><a href="#cancel_rpp" id="rpp_new_cancel" class="rpp_new_cancel">{t _cancel_adding_new_recurring_product_period}</a></li>
	</ul>
</fieldset>

<fieldset style="display: none;" class="addForm rpp_new_form" id="rpp_new_form_[[product.ID]]">
	<legend>[[ capitalize({t _add_new_recurring_product_period}) ]]</legend>
	[[ partial('backend/recurringProductPeriod/form.tpl', ['recurringProductPeriod': newRecurringProductPeriod, 'form': newForm]) ]]
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

	Backend.RecurringProductPeriod.prototype.properties = {
		// link_update: "[[ url("backend.recurringProductPeriod/update") ]]",
		// link_create: "[[ url("backend.recurringProductPeriod/create") ]]",
		link_edit: "[[ url("backend.recurringProductPeriod/edit/_id_") ]]",
		link_remove: "[[ url("backend.recurringProductPeriod/delete/_id_") ]]",
		// link_sort: "[[ url("backend.recurringProductPeriod/sort") ]]",
		message_confirm_remove: "{t _confirm_removing_rpp}"
	};

	Event.observe($("rpp_new_show_[[product.ID]]"), "click", function(e)
	{
		e.preventDefault();
		var newForm = Backend.RecurringProductPeriod.prototype.getInstance( $("rpp_new_form_[[product.ID]]").down('form') );
		newForm.showNewForm();
	});
</script>