<ul style="display: none;">
	<li class="imageTemplate">
		{img class="image" src=""}
		<span class="imageTitle"></span>
	</li>
</ul>

<fieldset class="container" {denied role="category.update"}style="display: none"{/denied}>
	<ul class="menu" id="catImgMenu_[[ownerId]]">
		<li class="catImageAdd">
			<a href="#" id="catImgMenu_[[ownerId]]_add" class="pageMenu">{t _add_new}</a>
		</li>
		<li class="catImageAddCancel done" style="display: none">
			<a href="#" id="catImgMenu_[[ownerId]]_cancel" class="pageMenu">{t _cancel_new}</a>
		</li>
	</ul>
</fieldset>


<script type="text/javascript">
	Event.observe("catImgMenu_[[ownerId]]_add", "click", function(e)
	{
		e.preventDefault();
		var form = new ActiveForm.Slide(this.up("ul"));
		form.show("catImageAdd", "catImgAdd_[[ownerId]]");
	});

	Event.observe("catImgMenu_[[ownerId]]_cancel", "click", function(e)
	{
		e.preventDefault();
		var form = new ActiveForm.Slide(this.up("ul"));
		form.hide("catImageAdd", "catImgAdd_[[ownerId]]");
	});
</script>


<div id="catImgAdd_[[ownerId]]" class="catImageEditform style="display: none;">
{form handle=$form action="backend.categoryImage/upload" method="post" onsubmit="$('catImageList_`$ownerId`').handler.upload(this);" target="catImgUpload_`$ownerId`" method="POST" enctype="multipart/form-data" role="category.update"}

	<input type="hidden" name="ownerId" value="[[ownerId]]" />
	<input type="hidden" name="imageId" value="" />

	<fieldset class="addform">
		<legend>{t _add_new_title}</legend>

		{input name="image"}
			{label}{tip _image_file}:{/label}
			{filefield}
			<span class="maxFileSize">{maketext text=_max_file_size params=$maxSize}</span>
		{/input}

		[[ textfld('title', '_image_title') ]]

		{language}
			[[ textfld('title_`$lang.ID`', '_image_title') ]]
		{/language}

		<fieldset class="controls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="upload" class="submit" value="{t _upload}">
			{t _or}
			<a href="#" class="cancel">{t _cancel}</a>
		</fieldset>
	</fieldset>


	<script type="text/javascript">
		Element.observe($('catImgAdd_[[ownerId]]').down("a.cancel"), "click", function(e)
		{
			e.preventDefault();
			var form = ('catImgAdd_[[ownerId]]');

			$("catImageList_[[ownerId]]").handler.cancelAdd();

			var menu = new ActiveForm.Slide('catImgMenu_[[ownerId]]');
			menu.hide("catImageAdd", form);
		});
	</script>

{/form}

<iframe name="catImgUpload_[[ownerId]]" id="catImgUpload_[[ownerId]]" style="display: none"></iframe>
</div>

<ul id="catImageList_[[ownerId]]" class="catImageList {allowed role="category.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit">
	<li class="activeList_remove_sort activeList_remove_delete activeList_remove_edit ignore main">
		{t _main_image}
	</li>
	<li class="supplemental activeList_remove_sort activeList_remove_delete activeList_remove_edit ignore">
		{t _supplemental_images}
	</li>
</ul>

<div class="noRecords">
	<div>{t _no_images}</div>
</div>


<script type="text/javascript">
	var handler = new Backend.ObjectImage($("catImageList_[[ownerId]]"), 'cat');
	handler.initList([[images]]);

	handler.setDeleteUrl('{link controller="backend.categoryImage" action=delete}');
	handler.setSortUrl('{link controller="backend.categoryImage" action=saveOrder}');
	handler.setEditUrl('{link controller="backend.categoryImage" action=edit}');
	handler.setSaveUrl('{link controller="backend.categoryImage" action=save}');

	handler.setDeleteMessage('[[ addslashes({t _delete_confirm}) ]]');
	handler.setEditCaption('[[ addslashes({t _edit_image}) ]]');
	handler.setSaveCaption('[[ addslashes({t _save}) ]]');


	handler.activeListMessages =
	{
		_activeList_edit:	'[[ addslashes({t _activeList_edit}) ]]',
		_activeList_delete:  '[[ addslashes({t _activeList_delete}) ]]'
	}
</script>

