var result = {$result};
parent.document.getElementById('catImageList_{$ownerId}').handler.postUpload({$result});
parent.document.getElementById('catImageList_{$ownerId}').handler.hideProgressIndicator(parent.document.getElementById('catImageList_{$ownerId}').handler.addForm);
if(result.status == 'success') new parent.Backend.SaveConfirmationMessage("categoryImageSaved");