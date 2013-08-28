<script type="text/javascript">
	Backend.TaxClass.prototype.Links.update = "{link controller="backend.taxClass" action=update}";
	Backend.TaxClass.prototype.Links.create = "{link controller="backend.taxClass" action=create}";
	Backend.TaxClass.prototype.Links.edit = "{link controller="backend.taxClass" action=edit}";
	Backend.TaxClass.prototype.Links.remove = "{link controller="backend.taxClass" action=delete}";
	Backend.TaxClass.prototype.Links.sort = "{link controller="backend.taxClass" action=sort}";
	Backend.TaxClass.prototype.Messages.enabled = "{t _enabled}";
	Backend.TaxClass.prototype.Messages.disabled = "{t _disabled}";
	Backend.TaxClass.prototype.Messages.confirmRemove = "{t _are_you_sure_you_want_to_remove_ths_class}";
</script>

[[ partial("backend/taxClass/classes.tpl") ]]