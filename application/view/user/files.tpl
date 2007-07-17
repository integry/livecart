<div class="userOrders">

{include file="layout/frontend/header.tpl"}

<div id="content" class="left right">
	
	<h1>{t _your_files}</h1>
	
	{include file="user/userMenu.tpl" current="fileMenu"}

    <div id="userContent">

        <div class="resultStats">
            {if $files}
                {maketext text="[quant,_1,file,files] found" params=$files|@count}
            {else}
                {t _no_files_found}
            {/if}
        </div>
        
        {foreach from=$files item="item"}        
            <h3>
                <a href="{link controller=user action=item id=$item.ID}">{$item.Product.name_lang}</a>
            </h3>
            {include file="user/fileList.tpl" item=$item}
        {/foreach}   
    
    </div>
    
</div>

{include file="layout/frontend/footer.tpl"}

</div>