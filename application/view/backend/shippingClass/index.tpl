{includeJs file="library/ActiveList.js"}
{includeJs file="backend/ShippingClass.js"}

{includeJs file="library/form/State.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/form/Validator.js"}

{includeCss file="backend/ShippingClass.css"}
{includeCss file="library/ActiveList.css"}
{includeCss file="library/TabControl.css"}

{pageTitle help="settings.classes"}{t _classes}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<script type="text/javascript">
	Backend.ShippingClass.prototype.Links.update = "[[ url("backend.shippingClass/update") ]]";
	Backend.ShippingClass.prototype.Links.create = "[[ url("backend.shippingClass/create") ]]";
	Backend.ShippingClass.prototype.Links.edit = "[[ url("backend.shippingClass/edit") ]]";
	Backend.ShippingClass.prototype.Links.remove = "[[ url("backend.shippingClass/delete") ]]";
	Backend.ShippingClass.prototype.Links.sort = "[[ url("backend.shippingClass/sort") ]]";
	Backend.ShippingClass.prototype.Messages.enabled = "{t _enabled}";
	Backend.ShippingClass.prototype.Messages.disabled = "{t _disabled}";
	Backend.ShippingClass.prototype.Messages.confirmRemove = "{t _are_you_sure_you_want_to_remove_ths_class}";
</script>

[[ partial("backend/shippingClass/classes.tpl") ]]

[[ partial("layout/backend/footer.tpl") ]]