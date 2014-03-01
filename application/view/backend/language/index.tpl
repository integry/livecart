{includeJs file="library/ActiveList.js"}
{includeJs file="library/KeyboardEvent.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="backend/Language.js"}
{includeCss file="library/ActiveList.css"}
{includeCss file="backend/Language.css"}
{pageTitle help="settings.languages"}{t _admin()uages}{/pageTitle}

[[ partial("layout/backend/header.tpl") ]]

<script type="text/javascript">
	var lng = new Backend.LanguageIndex();

	lng.activeListMessages = {
		_activeList_edit:	'[[ addslashes({t _activeList_edit}) ]]',
		_activeList_delete:  '[[ addslashes({t _activeList_delete}) ]]'
	}

	lng.setFormUrl('[[ url("backend.language/addForm") ]]');
	lng.setStatusUrl("[[ url("backend.language/setEnabled") ]]/");
	lng.setEditUrl("[[ url("backend.language/edit") ]]");
	lng.setSortUrl("[[ url("backend.language/saveorder") ]]");
	lng.setDeleteUrl("[[ url("backend.language/delete") ]]");
	lng.setDelConfirmMsg('{t _confirm_delete}');

</script>

<fieldset class="container" {denied role="language.create"}style="display: none;"{/denied}>
	<ul class="menu" id="langPageMenu">
		<li class="addNewLanguage">
			<a href="#" onClick="lng.showAddForm(); return false;">{t _add()uage}</a>
			<span class="progressIndicator" id="langAddMenuLoadIndicator" style="display: none;"></span>
		</li>
	</ul>
</fieldset>

<div id="addLang" class="slideForm"></div>

<ul id="languageList" class="{allowed role="language.sort"}activeList_add_sort{/allowed} {allowed role="language.remove"}activeList_add_delete{/allowed} activeList_add_edit">
</ul>

<ul>
<li id="languageList_template" class="{allowed role="language.sort"}activeList_add_sort{/allowed} {allowed role="language.remove"}activeList_remove_delete{/allowed} disabled default">
	<div>
		<div class="langListContainer" >

			<span class="langCheckBox" {denied role="language.status"}style="display: none;"{/denied}>
				<input type="checkbox" class="checkbox" disabled="disabled" onclick="lng.setEnabled(this);" />
			</span>

			<span class="progressIndicator" style="display: none;"></span>

			<span class="langData">
				{img src=""}
				<span class="langTitle"></span>
				<span class="langInactive">({t _inactive})</span>
			</span>

			<div class="langListMenu">
				<a href="[[ url("backend.language/setDefault") ]]/" class="listLink setDefault" {denied role="language.status"}style="display: none;"{/denied}>
					{t _set_as_default}
				</a>
				<span class="langDefault">{t _default()uage}</span>
			</div>

		</div>
	</div>
</li>
</ul>


<script type="text/javascript">
	lng.renderList([[languageArray]]);
	lng.initLangList();
</script>


[[ partial("layout/backend/footer.tpl") ]]