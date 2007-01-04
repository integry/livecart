<ul style="display: none;">
	<li id="catImageTemplate">
		<img /> <span class="catImageTitleDefLang"></span>
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
{form handle=$form action="controller=backend.categoryimage action=upload" method="post" onsubmit="catImg.upload(this); return false;"}
	
	<fieldset>	
		<legend>{t _add_new}</legend>
		<p>
			<label for="image">{t _image_file}</label>
			{filefield name="image" id="image"}	
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
		
			<input type="submit" class="submit" value="{tn _upload}"> {t _or} <a href="#" class="cancel" onclick="restoreMenu('catImgAdd_{$catId}', 'catImgMenu_{$catId}'); return false;">{t _cancel}</a>
	</fieldset>

{/form}
</div>

<ul id="catImageList_{$catId}"></ul>