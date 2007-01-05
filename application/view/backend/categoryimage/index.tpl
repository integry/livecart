<ul style="display: none;">
	<li class="catImageTemplate">
		<img src="" /> <span class="catImageTitleDefLang"></span>
		<div class="catImageTitleAll">
			<span></span>
		</div>
	</li>
</ul>

<ul class="menu" id="catImgMenu_{$catId}">
	<li>
		<a href="#" onclick="slideForm('catImgAdd_{$catId}', 'catImgMenu_{$catId}'); return false;" class="pageMenu">{t _add_new}</a>
	</li>	
</ul>

<div id="catImgAdd_{$catId}" style="display: none;">
{form handle=$form action="controller=backend.categoryimage action=upload" method="post" onsubmit="Backend.Category.image.upload(this);" target="catImgUpload_`$catId`" method="POST" enctype="multipart/form-data"}
	
	<input type="hidden" name="catId" value="{$catId}" />
	
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
			<input type="submit" class="submit" value="{tn _upload}"> {t _or} <a href="#" class="cancel" onclick="restoreMenu('catImgAdd_{$catId}', 'catImgMenu_{$catId}'); return false;">{t _cancel}</a>
	</fieldset>

{/form}
<iframe name="catImgUpload_{$catId}" id="catImgUpload_{$catId}"></iframe>
</div>

<ul id="catImageList_{$catId}" class="catImageList activeList_add_delete activeList_add_edit activeList_add_sort"></ul>

<script type="text/javascript">
	Backend.Category.image.initList({$catId}, {$images});
</script>