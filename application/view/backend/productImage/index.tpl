<ul style="display: none;">
	<li class="imageTemplate">
		{img class="image" src=""}
		<span class="imageTitle"></span>
	</li>
</ul>

<fieldset class="container" {denied role="product.update"}style="display: none"{/denied}>
	<ul class="menu" id="prodImgMenu_{$ownerId}">
		<li>
			<a href="#" onclick="slideForm('prodImgAdd_{$ownerId}', 'prodImgMenu_{$ownerId}'); return false;" class="pageMenu">{t _add_new}</a>
		</li>	
	</ul>
</fieldset>

<div id="prodImgAdd_{$ownerId}" class="prodImageEditForm" style="display: none;">
{form handle=$form action="controller=backend.productImage action=upload" method="post" onsubmit="$('prodImageList_`$ownerId`').handler.upload(this);" target="prodImgUpload_`$ownerId`" method="POST" enctype="multipart/form-data" role="product.update"}
	
	<input type="hidden" name="ownerId" value="{$ownerId}" />
	<input type="hidden" name="imageId" value="" />
		
	<fieldset>	
		<legend>{t _add_new}</legend>
		<p class="required">
			<label for="image">{t _image_file}</label>
			<fieldset class="error">
				{filefield name="image" id="image"}
				<div class="errorText" style="display: none;"></div>
			</fieldset>
		</p>
			
		<p>
			<label for="title">{t _image_title}</label>
			{textfield name="title" id="title"}	
		</p>		
		
		{language}
			<p>
				<label>{t _image_title}</label>
				{textfield name="title_`$lang.ID`"}
			</p>
		{/language}		
	
        <fieldset class="controls">	
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="upload" class="submit" value="{tn _upload}"> 
            {t _or} 
            <a href="#" class="cancel" onclick="restoreMenu('prodImgAdd_{$ownerId}', 'prodImgMenu_{$ownerId}'); return false;">{t _cancel}</a>
        </fieldset>
	</fieldset>

{/form}
<iframe name="prodImgUpload_{$ownerId}" id="prodImgUpload_{$ownerId}" style="display: none;"></iframe>
</div>

<ul id="prodImageList_{$ownerId}" class="prodImageList {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit">
    <p class="main">{t _main_image}</p><p class="supplemental">{t _supplemental_images}</p>
</ul>

<div class="noRecords">
	<div>{t _no_images}</div>
</div>

<script type="text/javascript">

    var handler = new Backend.ObjectImage($("prodImageList_{$ownerId}"), 'prod');    
	handler.initList({$images});
	
	handler.setDeleteUrl('{link controller=backend.productImage action=delete}');	
	handler.setSortUrl('{link controller=backend.productImage action=saveOrder}');	
	handler.setEditUrl('{link controller=backend.productImage action=edit}');		
	handler.setSaveUrl('{link controller=backend.productImage action=save}');		
	   
	handler.setDeleteMessage('{t _delete_confirm|addslashes}');	
	handler.setEditCaption('{t _edit_image|addslashes}');	
	handler.setSaveCaption('{t _save|addslashes}');	
    
    {literal}
    handler.activeListMessages = 
    { 
        _activeList_edit:    {/literal}'{t _activeList_edit|addslashes}'{literal},
        _activeList_delete:  {/literal}'{t _activeList_delete|addslashes}'{literal}
    }
    {/literal}
	
</script>