<fieldset class="container">
	<ul class="menu" id="class_new_menu">
		<li class="addClass"><a href="#new_class" id="class_new_show">{t _add_new_class}</a></li>
		<li class="done addClassCancel" style="display: none"><a href="#cancel_class" id="class_new_cancel">{t _cancel_adding_new_class}</a></li>
	</ul>
</fieldset>

<fieldset id="class_new_form" style="display: none;" class="addForm">
	<legend>[[ capitalize({t _add_new_class}) ]]</legend>
	[[ partial('backend/shippingClass/class.tpl', ['class': newClass, 'classForm': newClassForm]) ]]
</fieldset>

<ul class="activeList activeList_add_delete activeList_add_sort activeList_add_edit class_classesList" id="class_classesList" >
{foreach from=$classesForms key="key" item="classForm"}
	<li id="class_classesList_{$classes[$key].ID}">

	<span class="error class_viewMode">{$classes[$key].name}</span>

	</li>
{/foreach}
</ul>





<script type="text/javascript">
	Event.observe($("class_new_show"), "click", function(e)
	{
		e.preventDefault();
		var newForm = Backend.ShippingClass.prototype.getInstance( $("class_new_form").down('form') );
		newForm.showNewForm();
	});

	ActiveList.prototype.getInstance("class_classesList", Backend.ShippingClass.prototype.Callbacks, function() {});
</script>

