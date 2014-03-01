var result = [[result]];
{*
parent.document.getElementById('prodImageList_[[ownerId]]').handler.postUpload([[result]]);
parent.document.getElementById('prodImageList_[[ownerId]]').handler.hideProgressIndicator(parent.document.getElementById('prodImageList_[[ownerId]]').handler.addForm);
*}
parent.Backend.Product.hideQuickEditAddImageForm(parent.('product_[[ownerId]]_quick_form').down('ul').down('li',1), [[ownerId]]);


if(result.status == 'success')
{
	new parent.Backend.SaveConfirmationMessage("productImageSaved");
}
else if(result.status == 'failure')
{
	new parent.Backend.SaveConfirmationMessage("productImageSaveFailure");
}
