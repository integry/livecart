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

{literal}
<script type="text/javascript">
	Event.observe("{/literal}catImgMenu_[[ownerId]]_add{literal}", "click", function(e)
	{
		e.preventDefault();
		var form = new ActiveForm.Slide(this.up("ul"));
		form.show("catImageAdd", "{/literal}catImgAdd_[[ownerId]]{literal}");
	});

	Event.observe("{/literal}catImgMenu_[[ownerId]]_cancel{literal}", "click", function(e)
	{
		e.preventDefault();
		var form = new ActiveForm.Slide(this.up("ul"));
		form.hide("catImageAdd", "{/literal}catImgAdd_[[ownerId]]{literal}");
	});
</script>
{/literal}

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
			<input type="submit" name="upload" class="submit" value="{tn _upload}">
			{t _or}
			<a href="#" class="cancel">{t _cancel}</a>
		</fieldset>
	</fieldset>

	{literal}
	<script type="text/javascript">
		Element.observe($('{/literal}catImgAdd_[[ownerId]]{literal}').down("a.cancel"), "click", function(e)
		{
			e.preventDefault();
			var form = ('{/literal}catImgAdd_[[ownerId]]{literal}');

			$("{/literal}catImageList_[[ownerId]]{literal}").handler.cancelAdd();

			var menu = new ActiveForm.Slide('{/literal}catImgMenu_[[ownerId]]{literal}');
			menu.hide("catImageAdd", form);
		});
	</script>
	{/literal}
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

{literal}
<script type="text/javascript">
	var handler = new Backend.ObjectImage($("{/literal}catImageList_[[ownerId]]{literal}"), 'cat');
	handler.initList({/literal}[[images]]{literal});

	handler.setDeleteUrl('{/literal}{link controller="backend.categoryImage" action=delete}{literal}');
	handler.setSortUrl('{/literal}{link controller="backend.categoryImage" action=saveOrder}{literal}');
	handler.setEditUrl('{/literal}{link controller="backend.categoryImage" action=edit}{literal}');
	handler.setSaveUrl('{/literal}{link controller="backend.categoryImage" action=save}{literal}');

	handler.setDeleteMessage('{/literal}{t _delete_confirm|addslashes}{literal}');
	handler.setEditCaption('{/literal}{t _edit_image|addslashes}{literal}');
	handler.setSaveCaption('{/literal}{t _save|addslashes}{literal}');


	handler.activeListMessages =
	{
		_activeList_edit:	'{/literal}{t _activeList_edit|addslashes}{literal}',
		_activeList_delete:  '{/literal}{t _activeList_delete|addslashes}{literal}'
	}
</script>
{/literal}
