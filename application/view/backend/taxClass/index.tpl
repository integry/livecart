<script type="text/javascript">
	Backend.TaxClass.prototype.Links.update = "[[ url("backend.taxClass/update") ]]";
	Backend.TaxClass.prototype.Links.create = "[[ url("backend.taxClass/create") ]]";
	Backend.TaxClass.prototype.Links.edit = "[[ url("backend.taxClass/edit") ]]";
	Backend.TaxClass.prototype.Links.remove = "[[ url("backend.taxClass/delete") ]]";
	Backend.TaxClass.prototype.Links.sort = "[[ url("backend.taxClass/sort") ]]";
	Backend.TaxClass.prototype.Messages.enabled = "{t _enabled}";
	Backend.TaxClass.prototype.Messages.disabled = "{t _disabled}";
	Backend.TaxClass.prototype.Messages.confirmRemove = "{t _are_you_sure_you_want_to_remove_ths_class}";
</script>

[[ partial("backend/taxClass/classes.tpl") ]]