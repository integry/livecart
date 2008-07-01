var result = {$result};
parent.document.getElementById('manImageList_{$ownerId}').handler.postSave({$imageId}, {$result});
if(result.status == 'success') new parent.Backend.SaveConfirmationMessage("productImageSaved");