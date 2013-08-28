var result = [[result]];
parent.document.getElementById('prodImageList_[[ownerId]]').handler.postUpload([[result]]);
parent.document.getElementById('prodImageList_[[ownerId]]').handler.hideProgressIndicator(parent.document.getElementById('prodImageList_[[ownerId]]').handler.addForm);
if(result.status == 'success') new parent.Backend.SaveConfirmationMessage("productImageSaved");
if(result.status == 'failure') new parent.Backend.SaveConfirmationMessage("productImageSaveFailure");