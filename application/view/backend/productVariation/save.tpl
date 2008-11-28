<script type="text/javascript">
	var editor = parent.Backend.ProductVariation.Editor.prototype.getInstance({$parent});
	editor.updateIDs({json array=$ids});
	editor.updateImages({json array=$images});
	parent.TabControl.prototype.getInstance('productManagerContainer').setCounter('tabProductVariations', {$variationCount});
	parent.Backend.SaveConfirmationMessage.prototype.showMessage('{t _variations_save_conf|escape}');
</script>