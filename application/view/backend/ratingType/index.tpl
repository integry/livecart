{allowed role="product.create"}

	<ul class="menu addTypeMenu">
		<li class="addRatingType"><a href="#add" class="addRatingTypeLink">{t _add_rating_type}</a></li>
		<li class="addRatingTypeCancel done" style="display: none;"><a href="#cancel" class="addRatingTypeCancelLink">{t _cancel_adding_rating_type}</a></li>
	</ul>

{/allowed}

<fieldset class="slideForm addForm addRatingTypeform style="display: none;">

	<legend>{t _add_rating_type|capitalize}</legend>

	{form action="backend.ratingType/add" method="POST" onsubmit="new Backend.RatingType.Add(this); return false;" handle=$form class="enabled ratingTypeform}
		<input type="hidden" name="categoryId" value="[[id]]" />
		<input type="hidden" name="id" />

		[[ textfld('name', '_name') ]]

		{language}
			[[ textfld('name_`$lang.ID`', '_name') ]]
		{/language}

		<fieldset class="controls" {denied role="news"}style="display: none;"{/denied}>
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit save" value="{tn _save}" />
			<input type="submit" class="submit add" value="{tn _add}" />
			{t _or} <a class="cancel" href="#" onclick="Backend.RatingType.prototype.hideAddForm(); return false;">{t _cancel}</a>
		</fieldset>
	{/form}

</fieldset>

<ul id="typeList_[[id]]" class="activeList {allowed role="news.sort"}activeList_add_sort{/allowed} {allowed role="news.delete"}activeList_add_delete{/allowed} {allowed role="news.update"}activeList_add_edit{/allowed} typeList">
</ul>
<div style="display: none">
	<span class="deleteUrl">{link controller="backend.ratingType" action=delete}?id=</span>
	<span class="confirmDelete">{t _del_conf}</span>
	<span class="sortUrl">{link controller="backend.ratingType" action=saveOrder id=$id}</span>
	<span class="saveUrl">{link controller="backend.ratingType" action=save}</span>
</div>

<ul style="display: none;">
	<li class="typeList_template" id="typeList_template" style="position: relative;">
		<span class="activeListTitle newsTitle"></span>

		<div class="formContainer activeList_editContainer" style="display: none;"></div>

		<div class="clear"></div>
	</li>
</ul>

<script type="text/javascript">
	new Backend.RatingType({json array=$typeList}, $('tabRatingCategoriesContent_[[id]]'), $('typeList_template'));
</script>