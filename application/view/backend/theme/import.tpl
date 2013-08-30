{% if $status == 'success' %}
	{literal}
	parent.pageHandler.hideImportForm();
	parent.LiveCart.AjaxRequest.prototype.showConfirmation({status:'success', message:'{/literal}{% if $message %}{$message|escape}{% else %}{maketext text=_theme_imported params=$id}{% endif %}{literal}'});
	var id = "{/literal}{$id|escape}{literal}";
	if (parent.pageHandler.treeBrowser.selectItem(id) === 0)
	{
		var z = parent.pageHandler.treeBrowser.insertNewItem(0, id, id, null, 0, 0, 0, '', 1);
		if(z != -1)
		{
			new parent.Effect.Highlight(parent.$(z.tr));
			parent.pageHandler.treeBrowser.selectItem(z.id);
		}
	}
	parent.pageHandler.activateCategory(id);
	parent.pageHandler.showControls();
	{/literal}
{% else %}
	{literal}
	parent.LiveCart.AjaxRequest.prototype.showConfirmation({status:'failure', message:'{/literal}{% if $message %}{$message|escape}{% else %}{t _failed_to_import_theme}{% endif %}{literal}'});
	{/literal}
{% endif %}
