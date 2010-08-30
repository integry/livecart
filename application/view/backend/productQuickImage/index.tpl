<div id="prodImgAdd_{$ownerId}" class="prodImageEditForm">
{form handle=$form action="controller=backend.productQuickImage action=upload" method="post" target="prodImgUpload_`$ownerId`" method="POST" enctype="multipart/form-data" role="product.update"}
	<input type="hidden" name="ownerId" value="{$ownerId}" />
	<input type="hidden" name="imageId" value="" />
	<fieldset class="addForm">
		<legend>{t _add_new_image}</legend>
		
		<p class="required">
			<label for="image">{t _image_file}</label>
			<fieldset class="error">
				{filefield name="image" id="image"}
				<div/>
				<span class="maxFileSize">{maketext text=_max_file_size params=$maxSize}</span>
				<div class="errorText" style="display: none;"></div>
			</fieldset>
		</p>

		{if $images|@json_decode}
			<p>
				<label for="setAsMainImage{$ownerId}">{t _image_set_as_main}:</label>
				{checkbox class="checkbox" name="setAsMainImage" id="setAsMainImage`$ownerId`"}
			</p>
		{/if}
{*
		<p>
			<label for="title">{t _image_title}:</label>
			{textfield name="title" id="title"}	
		</p>
		{language}
			<p>
				<label>{t _image_title}:</label>
				{textfield name="title_`$lang.ID`"}
			</p>
		{/language}
*}

<p><label></label></p>
		<fieldset class="controls">	
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="upload" class="submit" value="{tn _upload}"> 
			{t _or} 
			<a href="javascript:void(0);" 
			onclick="return Backend.Product.hideQuickEditAddImageForm($('product_{$ownerId}_quick_form').down('ul').down('li',1), {$ownerId});" class="cancel" >{t _cancel}</a>
		</fieldset>
	</fieldset>

{/form}
<iframe name="prodImgUpload_{$ownerId}" id="prodImgUpload_{$ownerId}" style="display: none;"></iframe>
</div>

<ul id="prodImageList_{$ownerId}" class="hidden prodImageList {allowed role="product.update"}activeList_add_sort activeList_add_delete{/allowed} activeList_add_edit">
	<li class="activeList_remove_sort activeList_remove_delete activeList_remove_edit ignore main">
		{t _main_image}
	</li>
	<li class="activeList_remove_sort activeList_remove_delete activeList_remove_edit ignore supplemental">
		{t _supplemental_images}
	</li>
</ul>