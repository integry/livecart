{capture}
	{form handle=productForm}
	{capture assign="specField"}
		[[ partial("backend/product/form/specFieldList.tpl") ]]
	{/capture}
	{/form}
{/capture}

{ldelim} "status": "success", "message": "[[ addslashes({t _product_information_was_successfully_saved}) ]]", "specFieldHtml": {json array=specField}{rdelim}