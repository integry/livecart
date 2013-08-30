<div id="prodImgAdd_[[ownerId]]" class="prodImageEditForm">
{form handle=$form action="backend.productQuickImage/upload" method="post" target="prodImgUpload_`$ownerId`" method="POST" enctype="multipart/form-data" role="product.update"}
	<input type="hidden" name="ownerId" value="[[ownerId]]" />
	<input type="hidden" name="imageId" value="" />
	<fieldset class="addForm">
		<legend>{t _add_new_image}</legend>

		{input name="image"}
			{label}{t _image_file}:{/label}
			{filefield id="image"}
			<div/>
			<span class="maxFileSize">{maketext text=_max_file_size params=$maxSize}</span>
			<div class="errorText" style="display: none;"></div>
		{/input}

		{% if $images|@json_decode %}
			[[ checkbox('setAsMainImage', '_image_set_as_main') ]]
		{% endif %}

		<fieldset class="container">
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="upload" class="submit" value="{tn _upload}">
			{t _or}
			<a href="javascript:void(0);"
			onclick="return Backend.Product.hideQuickEditAddImageForm($('product_[[ownerId]]_quick_form').down('ul').down('li',1), [[ownerId]]);" class="cancel" >{t _cancel}</a>
		</fieldset>
	</fieldset>

{/form}
<iframe name="prodImgUpload_[[ownerId]]" id="prodImgUpload_[[ownerId]]" style="display: none;"></iframe>
</div>

<ul id="prodImageList_[[ownerId]]" class="hidden prodImageList {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit">
	<li class="activeList_remove_sort activeList_remove_delete activeList_remove_edit ignore main">
		{t _main_image}
	</li>
	<li class="activeList_remove_sort activeList_remove_delete activeList_remove_edit ignore supplemental">
		{t _supplemental_images}
	</li>
</ul>