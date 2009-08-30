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
{include file="layout/backend/header.tpl"}

<script type="text/javascript">
	Backend.ShippingClass.prototype.Links.update = "{link controller=backend.shippingClass action=update}";
	Backend.ShippingClass.prototype.Links.create = "{link controller=backend.shippingClass action=create}";
	Backend.ShippingClass.prototype.Links.edit = "{link controller=backend.shippingClass action=edit}";
	Backend.ShippingClass.prototype.Links.remove = "{link controller=backend.shippingClass action=delete}";
	Backend.ShippingClass.prototype.Links.sort = "{link controller=backend.shippingClass action=sort}";
	Backend.ShippingClass.prototype.Messages.enabled = "{t _enabled}";
	Backend.ShippingClass.prototype.Messages.disabled = "{t _disabled}";
	Backend.ShippingClass.prototype.Messages.confirmRemove = "{t _are_you_sure_you_want_to_remove_ths_class}";
</script>

{include file="backend/shippingClass/classes.tpl"}

{include file="layout/backend/footer.tpl"}