var result = {$result};
{* parent.document.getElementById('prodImageList_{$ownerId}').handler.postSave({$imageId}, {$result}); *}
if(result.status == 'success') new parent.Backend.SaveConfirmationMessage("productImageSaved");