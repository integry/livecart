<div id="staticPageContainer" ng-controller="StaticPageController" ng-init="setTree([[ json(pages) ]])">
	<div class="row">
	<div class="treeContainer col-sm-3">
		[[ partial('block/backend/tree.tpl', ['sortable': true]) ]]

		<ul class="verticalMenu">
			<li id="addMenu" class="addTreeNode"><a ng-click="add()">{t _add_new}</a></li>
			<li id="removeMenu" ng-show="activeID" class="removeTreeNode"><a ng-click="remove()">{t _remove}</a></li>
		</ul>
	</div>

	<div class="treeManagerContainer col-sm-9">

		<tabset>
			<tab ng-repeat="(index,vals) in pages" ng-click="selectID(vals.ID)" heading="{{getTabTitle(vals)}}">
				{#
				<div ng-show="vals.ID">
					<ul class="menu" id="staticPageMenu">
						<li id="codeMenu">
							<a class="menu" ng-click="showcode = !showcode">{t _show_template_code}</a>
						</li>
					</ul>

					<fieldset id="templateCode" ng-show="showcode">
						<legend>{t _template_code}</legend>
						{t _code_explain}:
						<br /><br />
						&lt;a href="<strong>{ldelim}pageUrl id={{vals.ID}}{rdelim}</strong>"&gt;<strong>{ldelim}pageName id={{vals.ID}}{rdelim}</strong>&lt;/a&gt;
					</fieldset>
				</div>
				#}

				[[ form('', ['ng-init': ';', 'ng-submit' : "save(index)"]) ]] >

				<div id="editContainer">
				
					[[ textfld('title', '_title') ]]

{#
					{input name="title"}
						{label}{t _title}:{/label}
						{% if $page.ID %}
							{textfield class="wider" id="title_`$page.ID`"}
						{% else %}
							{textfield class="wider" id="title_`$page.ID`" onkeyup="$('handle').value = ActiveForm.prototype.generateHandle(this.value);"}
						{% endif %}
					{/input}

					<p>{t _add_page_to_menu}</p>
					[[ checkbox('menuInformation', '_information_menu') ]]
					[[ checkbox('menuRootCategories', '_main_header_menu') ]]
#}

					[[ textfld('handle', '_handle') ]]
					
					[[ textareafld('text', '_text', ['ui-my-tinymce': '']) ]]

					[[ textareafld('metaDescription', '_meta_description') ]]

					{# [[ partial('backend/eav/fields.tpl', ['item': page, 'angular': "vals"]) ]] #}

					{#
					{language}
						[[ textfld('title_`$lang.ID`', '_title') ]]

						{input name="text_`$lang.ID`"}
							{label}{t _text}:{/label}
							{textarea tinymce=true class="tinyMCE longDescr" style="width: 100%;"}
						{/input}

						{input name="metaDescription_`[[lang.ID]]`"}
							{label}{t _meta_description}:{/label}
							{textarea style="width: 100%; height: 4em;"}
						{/input}

						[[ partial('backend/eav/fields.tpl', ['angular': "vals", 'item': page, 'language': lang.ID]) ]]
					{/language}
					#}

				</div>

				<div class="row">
					<div class="col-sm-12">
						<submit>{t _save}</submit>
						{t _or}
						<a class="cancel" id="cancel" ng-click="cancel(vals)">{t _cancel}</a>
					</div>
				</div>

				</form>
			</tab>
		</tabset>
	</div>
</div>
