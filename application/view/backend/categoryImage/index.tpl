<ul style="display: none;">
	<li class="catImageTemplate">
		<img class="catImage" src="" /> <span class="catImageTitleDefLang"></span>
		<span class="catImageTitle">
		</span>
	</li>
</ul>

<ul class="menu" id="catImgMenu_{$catId}">
	<li>
		<a href="#" onclick="slideForm('catImgAdd_{$catId}', 'catImgMenu_{$catId}'); return false;" class="pageMenu">{t _add_new}</a>
	</li>	
</ul>

<div id="catImgAdd_{$catId}" class="catImageEditForm" style="display: none;">
{form handle=$form action="controller=backend.categoryImage action=upload" method="post" onsubmit="Backend.Category.image.upload(this);" target="catImgUpload_`$catId`" method="POST" enctype="multipart/form-data"}
	
	<input type="hidden" name="catId" value="{$catId}" />
	<input type="hidden" name="imageId" value="" />
		
	<fieldset>	
		<legend>{t _add_new}</legend>
		<p>
			<label for="image">{t _image_file}</label>
			<fieldset class="error">
				{filefield name="image" id="image"}
				<div></div>
				<div class="errorText" style="display: none;"></div>
			</fieldset>
		</p>
			
		<p>
			<label for="title">{t _image_title}</label>
			{textfield name="title" id="title"}	
		</p>		
		
		<p>
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
		</p>		
		
			<span id="imgSaveIndicator_{$catId}" class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="upload" class="submit" value="{tn _upload}"> {t _or} <a href="#" class="cancel" onclick="restoreMenu('catImgAdd_{$catId}', 'catImgMenu_{$catId}'); return false;">{t _cancel}</a>
	</fieldset>

{/form}
<iframe name="catImgUpload_{$catId}" id="catImgUpload_{$catId}"></iframe>
</div>

<ul id="catImageList_{$catId}" class="catImageList activeList_add_sort activeList_add_delete activeList_add_edit"></ul>

<div id="catNoImages_{$catId}" class="noRecords">
	<div>{t _no_images}</div>
</div>

<script type="text/javascript">
    
    {literal}
    Backend.Category.image.activeListMessages = 
    { 
        _activeList_edit:    {/literal}'{t _activeList_edit}'{literal},
        _activeList_delete:  {/literal}'{t _activeList_delete}'{literal}
    }
    {/literal}

	Backend.Category.image.initList({$catId}, {$images});
	Backend.Category.image.setDeleteUrl('{link controller=backend.categoryImage action=delete}');	
	Backend.Category.image.setSortUrl('{link controller=backend.categoryImage action=saveOrder}');	
	Backend.Category.image.setEditUrl('{link controller=backend.categoryImage action=edit}');		
	Backend.Category.image.setSaveUrl('{link controller=backend.categoryImage action=save}');		
	   
	{capture name=delconf}{t _delete_confirm}{/capture}
	Backend.Category.image.setDeleteMessage({json array=$smarty.capture.delconf});	

	{capture name=editcapt}{t _edit_image}{/capture}
	Backend.Category.image.setEditCaption({json array=$smarty.capture.editcapt});	

	{capture name=savecapt}{t _save}{/capture}
	Backend.Category.image.setSaveCaption({json array=$smarty.capture.savecapt});	

</script>