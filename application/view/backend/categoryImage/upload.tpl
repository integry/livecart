parent.document.getElementById('catImageList_{$ownerId}').handler.postUpload({$result});
parent.document.getElementById('catImageList_{$ownerId}').handler.hideProgressIndicator(parent.document.getElementById('catImageList_{$ownerId}').handler.addForm);
parent.console.info({$result})
if({$result}.status == 'success') new parent.Backend.SaveConfirmationMessage(parent.$("categoryImageSaved"));