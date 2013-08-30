<ul style="display: none;">
	<li class="imageTemplate">
		{img class="image" src=""}
		<span class="imageTitle"></span>
	</li>
</ul>

<fieldset class="container" {denied role="product.update"}style="display: none"{/denied}>
	<ul class="menu" id="manImgMenu_[[ownerId]]">
		<li class="manImageAdd"><a href="#" id="manImageAdd_[[ownerId]]_add" class="pageMenu">{t _add_new}</a></li>
		<li class="manImageAddCancel done" style="display: none;"><a href="#" id="manImageAdd_[[ownerId]]_cancel">{t _cancel_new}</a></li>
	</ul>
</fieldset>


<script type="text/javascript">
	Event.observe("manImageAdd_[[ownerId]]_add", "click", function(e)
	{
		e.preventDefault();
		var form = new ActiveForm.Slide(this.up("ul"));
		form.show("manImageAdd", "manImgAdd_[[ownerId]]");
	});

	Event.observe("manImageAdd_[[ownerId]]_cancel", "click", function(e)
	{
		e.preventDefault();
		var form = new ActiveForm.Slide(this.up("ul"));
		form.hide("manImageAdd", "manImgAdd_[[ownerId]]");
	});
</script>


<div id="manImgAdd_[[ownerId]]" class="manImageEditForm" style="display: none;">
{form handle=$form action="backend.manufacturerImage/upload" method="post" onsubmit="$('manImageList_`$ownerId`').handler.upload(this);" target="manImgUpload_`$ownerId`" method="POST" enctype="multipart/form-data" role="product.update"}

	<input type="hidden" name="ownerId" value="[[ownerId]]" />
	<input type="hidden" name="imageId" value="" />

	<fieldset class="addForm">
		<legend>{t _add_new_title}</legend>

		{input name="image"}
			{label}{t _image_file}:{/label}
			{filefield}
			<div class="maxFileSize">{maketext text=_max_file_size params=$maxSize}</div>
		{/input}

		[[ textfld('title', '_image_title') ]]

		{language}
			[[ textfld('title_`$lang.ID`', '_image_title') ]]
		{/language}

		<fieldset class="controls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="upload" class="submit" value="{t _upload}">
			{t _or}
			<a href="#" class="cancel" >{t _cancel}</a>
		</fieldset>
	</fieldset>


	<script type="text/javascript">
		Element.observe($('manImgAdd_[[ownerId]]').down("a.cancel"), "click", function(e)
		{
			e.preventDefault();
			var form = ('manImgAdd_[[ownerId]]');

			$("manImageList_[[ownerId]]").handler.cancelAdd();

			var menu = new ActiveForm.Slide('manImgMenu_[[ownerId]]');
			menu.hide("manImageAdd", form);
		});
	</script>


{/form}
<iframe name="manImgUpload_[[ownerId]]" id="manImgUpload_[[ownerId]]" style="display: none;"></iframe>
</div>

<ul id="manImageList_[[ownerId]]" class="manImageList {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit">
	<li class="activeList_remove_sort activeList_remove_delete activeList_remove_edit ignore main">
		{t _main_image}
	</li>
	<li class="activeList_remove_sort activeList_remove_delete activeList_remove_edit ignore supplemental">
		{t _supplemental_images}
	</li>
</ul>

<div class="noRecords">
	<div>{t _no_images}</div>
</div>


<script type="text/javascript">
	var handler = new Backend.ObjectImage($("manImageList_[[ownerId]]"), 'man');
	handler.initList([[images]]);

	handler.setDeleteUrl('[[ url("backend.manufacturerImage/delete") ]]');
	handler.setSortUrl('[[ url("backend.manufacturerImage/saveOrder") ]]');
	handler.setEditUrl('[[ url("backend.manufacturerImage/edit") ]]');
	handler.setSaveUrl('[[ url("backend.manufacturerImage/save") ]]');

	handler.setDeleteMessage('[[ addslashes({t _delete_confirm}) ]]');
	handler.setEditCaption('[[ addslashes({t _edit_image}) ]]');
	handler.setSaveCaption('[[ addslashes({t _save}) ]]');

	handler.activeListMessages =
	{
		_activeList_edit:	'[[ addslashes({t _activeList_edit}) ]]',
		_activeList_delete:  '[[ addslashes({t _activeList_delete}) ]]'
	}
</script>
