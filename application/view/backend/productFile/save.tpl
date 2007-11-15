<script type="text/javascript">

{if $status == 'failure'}
	window.frameElement.controller.model.errors = {json array=$errors};
	new parent.Backend.SaveConfirmationMessage("productFileSaveFailure");
{else}
	window.frameElement.controller.model.store('ID', {$productFile.ID});
	window.frameElement.controller.model.store('title', '{$productFile.title|addslashes}');
	window.frameElement.controller.model.store('fileName', '{$productFile.fileName|addslashes}');
	window.frameElement.controller.view.nodes.fileName.value = '{$productFile.fileName|addslashes}'; 
	window.frameElement.controller.model.store('extension', '{$productFile.extension|addslashes}');
	window.frameElement.controller.view.nodes.extension.update('.' + '{$productFile.extension|addslashes}');
	new parent.Backend.SaveConfirmationMessage("productFileSaved");
{/if}

window.frameElement.action.call(window.frameElement.controller, '{$status}');
</script>