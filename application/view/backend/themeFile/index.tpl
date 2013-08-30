
<fieldset class="container">
	<ul class="menu" id="uploadMenu_[[theme]]">
		<li class="fileUpload">
			<a href="#" id="uploadNewFile_[[theme]]_upload" class="pageMenu">{t _upload_new_file}</a>
		</li>
		<li class="fileUploadCancel done" style="display: none">
			<a href="#" id="uploadNewFile_[[theme]]_cancel" class="pageMenu">{t _cancel}</a>
		</li>
	</ul>
</fieldset>

<div id="themeFileForm_[[theme]]" class="slideForm addForm" style="display: none;">
	{form handle=$form action="backend.themeFile/upload"
		onsubmit=""
		target="fileUpload_`$theme`" method="POST" enctype="multipart/form-data"
		autocomplete="off"
		}

		<span class="progressIndicator" style="display: none;"></span>
		<input type="hidden" name="theme" value="[[theme]]" />
		<input type="hidden" name="orginalFileName" class="orginalFileName" value="" />

		{input name="file"}
			{label}{t _select_file}:{/label}
			{filefield value=""}
			<br />
			<span class="maxFileSize">{maketext text=_max_file_size params=$maxSize}</span>
		{/input}

		[[ textfld('filename', '_change_file_name', class: 'changeFileName') ]]

		<fieldset class="controls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="upload" class="submit" value="{t _upload}">
			{t _or}
			<a href="#" class="cancel">{t _cancel}</a>
		</fieldset>

	</fieldset>
	{/form}

	<iframe name="fileUpload_[[theme]]" id="fileUpload_[[theme]]" style="display: none"></iframe>

</div>




</div>

<ul id="filesList_[[theme]]" class="activeList activeList_add_delete activeList_add_edit">
</ul>

<div style="display: none">
	<span id="deleteUrl">{link controller="backend.themeFile" action=delete}?file=__FILE__&theme=__THEME__</span>
	<span id="confirmDelete">{t _del_conf}</span>
	{* <span id="saveUrl">{link controller="backend.siteNews" action=save}</span> *}
</div>

<ul style="display: none;">
	<li id="filesList_template_[[theme]]" style="position: relative;">
		<span class="progressIndicator" style="display: none; "></span>
		<span class="filesData">
			<input type="hidden" class="file" value="" />
			<input type="hidden" class="theme" value="" />

			<div class="thumbnailContainer">
				<a href="" rel="lightbox">
					<img src="" alt="">
				</a>
			</div>

			<div class="fileInfoContainer">
				<div class="fileName"></div>
				<div class="cssHintContainer">
					{t _including_as_css_background}:<br/><code>background-image: url('../../upload/theme/<span class="cssTheme"></span>/<span class="cssFile"></span>');</code>
				</div>
			</div>
		</span>
		<div class="formContainer activeList_editContainer" style="display: none;"></div>
		<div class="clear"></div>
	</li>
</ul>

<script type="text/javascript">
	new Backend.ThemeFile({json array=$filesList}, $('filesList_[[theme]]'), $('filesList_template_[[theme]]'));
</script>
