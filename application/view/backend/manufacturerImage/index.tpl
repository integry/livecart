<ul style="display: none;">
	<li class="imageTemplate">
		{img class="image" src=""}
		<span class="imageTitle"></span>
	</li>
</ul>

<fieldset class="container" {denied role="product.update"}style="display: none"{/denied}>
	<ul class="menu" id="manImgMenu_{$ownerId}">
		<li class="manImageAdd"><a href="#" id="manImageAdd_{$ownerId}_add" class="pageMenu">{t _add_new}</a></li>
		<li class="manImageAddCancel done" style="display: none;"><a href="#" id="manImageAdd_{$ownerId}_cancel">{t _cancel_new}</a></li>
	</ul>
</fieldset>

{literal}
<script type="text/javascript">
	Event.observe("{/literal}manImageAdd_{$ownerId}_add{literal}", "click", function(e)
	{
		e.preventDefault();
		var form = new ActiveForm.Slide(this.up("ul"));
		form.show("manImageAdd", "{/literal}manImgAdd_{$ownerId}{literal}");
	});

	Event.observe("{/literal}manImageAdd_{$ownerId}_cancel{literal}", "click", function(e)
	{
		e.preventDefault();
		var form = new ActiveForm.Slide(this.up("ul"));
		form.hide("manImageAdd", "{/literal}manImgAdd_{$ownerId}{literal}");
	});
</script>
{/literal}

<div id="manImgAdd_{$ownerId}" class="manImageEditForm" style="display: none;">
{form handle=$form action="controller=backend.manufacturerImage action=upload" method="post" onsubmit="$('manImageList_`$ownerId`').handler.upload(this);" target="manImgUpload_`$ownerId`" method="POST" enctype="multipart/form-data" role="product.update"}

	<input type="hidden" name="ownerId" value="{$ownerId}" />
	<input type="hidden" name="imageId" value="" />

	<fieldset class="addForm">
		<legend>{t _add_new_title}</legend>

		{input name="image"}
			{label}{t _image_file}:{/label}
			{filefield}
			<div class="maxFileSize">{maketext text=_max_file_size params=$maxSize}</div>
		{/input}

		{input name="title"}
			{label}{t _image_title}:{/label}
			{textfield}
		{/input}

		{language}
			{input name="title_`$lang.ID`"}
				{label}{t _image_title}:{/label}
				{textfield}
			{/input}
		{/language}

		<fieldset class="controls">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="upload" class="submit" value="{tn _upload}">
			{t _or}
			<a href="#" class="cancel" >{t _cancel}</a>
		</fieldset>
	</fieldset>

	{literal}
	<script type="text/javascript">
		Element.observe($('{/literal}manImgAdd_{$ownerId}{literal}').down("a.cancel"), "click", function(e)
		{
			e.preventDefault();
			var form = ('{/literal}manImgAdd_{$ownerId}{literal}');

			$("{/literal}manImageList_{$ownerId}{literal}").handler.cancelAdd();

			var menu = new ActiveForm.Slide('{/literal}manImgMenu_{$ownerId}{literal}');
			menu.hide("manImageAdd", form);
		});
	</script>
	{/literal}

{/form}
<iframe name="manImgUpload_{$ownerId}" id="manImgUpload_{$ownerId}" style="display: none;"></iframe>
</div>

<ul id="manImageList_{$ownerId}" class="manImageList {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit">
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

{literal}
<script type="text/javascript">
	var handler = new Backend.ObjectImage($("{/literal}manImageList_{$ownerId}{literal}"), 'man');
	handler.initList({/literal}{$images}{literal});

	handler.setDeleteUrl('{/literal}{link controller="backend.manufacturerImage" action=delete}{literal}');
	handler.setSortUrl('{/literal}{link controller="backend.manufacturerImage" action=saveOrder}{literal}');
	handler.setEditUrl('{/literal}{link controller="backend.manufacturerImage" action=edit}{literal}');
	handler.setSaveUrl('{/literal}{link controller="backend.manufacturerImage" action=save}{literal}');

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