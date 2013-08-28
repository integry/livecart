var result = [[result]];
parent.document.getElementById('catImageList_[[ownerId]]').handler.postSave([[imageId]], [[result]]);
if(result.status == 'success') new parent.Backend.SaveConfirmationMessage("categoryImageSaved");