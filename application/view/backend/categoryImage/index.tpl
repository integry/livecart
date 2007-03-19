<ul style="display: none;">
	<li class="imageTemplate">
		<img class="image" src="" />
		<span class="imageTitle"></span>
	</li>
</ul>

<fieldset class="container">
	<ul class="menu" id="catImgMenu_{$catId}">
		<li>
			<a href="#" onclick="slideForm('catImgAdd_{$catId}', 'catImgMenu_{$catId}'); return false;" class="pageMenu">{t _add_new}</a>
		</li>	
	</ul>
</fieldset>

<div id="catImgAdd_{$catId}" class="catImageEditForm" style="display: none;">
{form handle=$form action="controller=backend.categoryImage action=upload" method="post" onsubmit="$('catImageList_`$catId`').handler.upload(this);" target="catImgUpload_`$catId`" method="POST" enctype="multipart/form-data"}
	
	<input type="hidden" name="ownerId" value="{$catId}" />
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
		
		<div class="translations">
			{foreach from=$languageList key=lang item=langName}
			<fieldset class="expandingSection">
				<legend>{maketext text="_translate" params="$langName"}</legend>
				<div class="expandingSectionContent">
					<p>
						<label>{t _image_title}</label>
						{textfield name="title_$lang"}
					</p>
				</div>
			</fieldset>
			{/foreach}
			<script type="text/javascript">
				var expander = new SectionExpander();
			</script>
		</div>		
		
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="upload" class="submit" value="{tn _upload}"> {t _or} <a href="#" class="cancel" onclick="restoreMenu('catImgAdd_{$catId}', 'catImgMenu_{$catId}'); return false;">{t _cancel}</a>
	</fieldset>

{/form}
<iframe name="catImgUpload_{$catId}" id="catImgUpload_{$catId}"></iframe>
</div>

<ul id="catImageList_{$catId}" class="catImageList activeList_add_sort activeList_add_delete activeList_add_edit">
    <p class="main">{t _main_image}</p><p class="supplemental">{t _supplemental_images}</p>
</ul>

<div class="noRecords">
	<div>{t _no_images}</div>
</div>

<script type="text/javascript">

    var handler = new Backend.ObjectImage($("catImageList_{$catId}"), 'cat');    
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