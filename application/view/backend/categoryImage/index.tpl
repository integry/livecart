<ul style="display: none;">
	<li class="imageTemplate">
		<img class="image" src="" />
		<span class="imageTitle"></span>
	</li>
</ul>

<fieldset class="container" {denied role="category.update"}style="display: none"{/denied}>
	<ul class="menu" id="catImgMenu_{$ownerId}">
		<li>
			<a href="#" onclick="slideForm('catImgAdd_{$ownerId}', 'catImgMenu_{$ownerId}'); return false;" class="pageMenu">{t _add_new}</a>
		</li>	
	</ul>
</fieldset>

<div id="catImgAdd_{$ownerId}" class="catImageEditForm" style="display: none;">
{form handle=$form action="controller=backend.categoryImage action=upload" method="post" onsubmit="$('catImageList_`$ownerId`').handler.upload(this);" target="catImgUpload_`$ownerId`" method="POST" enctype="multipart/form-data" role="category.update"}
	
	<input type="hidden" name="ownerId" value="{$ownerId}" />
	<input type="hidden" name="imageId" value="" />
		
	<fieldset>	
		<legend>{t _add_new}</legend>
		<p class="required">			
			{err for="image"}
                {{label {t _image_file}: }}
				{filefield}
            {/err}
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
            <a href="#" class="cancel" onclick="restoreMenu('catImgAdd_{$ownerId}', 'catImgMenu_{$ownerId}'); return false;">{t _cancel}</a>
	    </fieldset>
    </fieldset>

{/form}
<script>console.info('{$ownerId}')</script>
<iframe name="catImgUpload_{$ownerId}" id="catImgUpload_{$ownerId}" style="display: none"></iframe>
</div>

<ul id="catImageList_{$ownerId}" class="catImageList {allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit">
    <p class="main">{t _main_image}</p><p class="supplemental">{t _supplemental_images}</p>
</ul>

<div class="noRecords">
	<div>{t _no_images}</div>
</div>

<script type="text/javascript">

    var handler = new Backend.ObjectImage($("catImageList_{$ownerId}"), 'cat');    
	handler.initList({$images});
	
	handler.setDeleteUrl('{link controller=backend.categoryImage action=delete}');	
	handler.setSortUrl('{link controller=backend.categoryImage action=saveOrder}');	
	handler.setEditUrl('{link controller=backend.categoryImage action=edit}');		
	handler.setSaveUrl('{link controller=backend.categoryImage action=save}');		
	   
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