{capture}
    {form handle=$productForm}
    {capture assign="specField"}
        {include file="backend/product/form/specFieldList.tpl"}
    {/capture}
    {/form}
{/capture}
    
{ldelim}'status': 'success', 'specFieldHtml': {json array=$specField}{rdelim}