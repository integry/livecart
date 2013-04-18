<ul style="display: none;">
	<li class="imageTemplate">
		{img class="image" src=""}
		<span class="imageTitle"></span>
	</li>
</ul>

<fieldset class="container" {denied role="product.update"}style="display: none"{/denied}>
	<ul class="menu" id="prodImgMenu_{$ownerId}">
		<li class="prodImageAdd"><a href="#" id="prodImageAdd_{$ownerId}_add" class="pageMenu">{t _add_new}</a></li>
		<li class="prodImageAddCancel done" style="display: none;"><a href="#" id="prodImageAdd_{$ownerId}_cancel">{t _cancel_new}</a></li>
	</ul>
</fieldset>

{literal}
<script type="text/javascript">
	Event.observe("{/literal}prodImageAdd_{$ownerId}_add{literal}", "click", function(e)
	{
		Event.stop(e);
		var form = new ActiveForm.Slide(this.up("ul"));
		form.show("prodImageAdd", "{/literal}prodImgAdd_{$ownerId}{literal}");
	});

	Event.observe("{/literal}prodImageAdd_{$ownerId}_cancel{literal}", "click", function(e)
	{
		Event.stop(e);
		var form = new ActiveForm.Slide(this.up("ul"));
		form.hide("prodImageAdd", "{/literal}prodImgAdd_{$ownerId}{literal}");
	});
</script>
{/literal}

<div id="prodImgAdd_{$ownerId}" class="prodImageEditForm" style="display: none;">
{form handle=$form action="controller=backend.productImage action=upload" method="post" onsubmit="$('prodImageList_`$ownerId`').handler.upload(this);" target="prodImgUpload_`$ownerId`" method="POST" enctype="multipart/form-data" role="product.update"}

	<input type="hidden" name="ownerId" value="{$ownerId}" />
	<input type="hidden" name="imageId" value="" />

	<fieldset class="addForm">
		<legend>{t _add_new_title}</legend>

		{input name="image"}
			{label}{t _image_file}:{/label}
			{filefield}
			<span class="maxFileSize">{maketext text=_max_file_size params=$maxSize}</span>
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
		Element.observe($('{/literal}prodImgAdd_{$ownerId}{literal}').down("a.cancel"), "click", function(e)
		{
			Event.stop(e);
			var form = ('{/literal}prodImgAdd_{$ownerId}{literal}');

			$("{/literal}prodImageList_{$ownerId}{literal}").handler.cancelAdd();

			var menu = new ActiveForm.Slide('{/literal}prodImgMenu_{$ownerId}{literal}');
			menu.hide("prodImageAdd", form);
		});
	</script>
	{/literal}

{/form}
<iframe name="prodImgUpload_{$ownerId}" id="prodImgUpload_{$ownerId}" style="display: none;"></iframe>
</div>

<ul id="prodImageList_{$ownerId}" class="prodImageList {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit">
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
	var handler = new Backend.ObjectImage($("{/literal}prodImageList_{$ownerId}{literal}"), 'prod');
	handler.initList({/literal}{$images}{literal});

	handler.setDeleteUrl('{/literal}{link controller="backend.productImage" action=delete}{literal}');
	handler.setSortUrl('{/literal}{link controller="backend.productImage" action=saveOrder}{literal}');
	handler.setEditUrl('{/literal}{link controller="backend.productImage" action=edit}{literal}');
	handler.setSaveUrl('{/literal}{link controller="backend.productImage" action=save}{literal}');

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
