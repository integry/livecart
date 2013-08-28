{includeJs file="backend/StaticPage.js"}

{includeCss file="backend/StaticPage.css"}

{pageTitle help="content.pages"}{t _static_pages}{/pageTitle}

{include file="backend/eav/includes.tpl"}
{include file="layout/backend/header.tpl"}

<div id="staticPageContainer" ng-controller="TreeController" ng-init="setTree({$pages|escape})">
	<div class="treeContainer">
		{include file="block/backend/tree.tpl" sortable=true}

		<ul class="verticalMenu">
			<li id="addMenu" class="addTreeNode" {denied role="page.create"}style="display: none;"{/denied}><a ng-click="add()">{t _add_new}</a></li>
			<li id="removeMenu" ng-show="activeID" class="removeTreeNode" {denied role="page.remove"}style="display: none;"{/denied}><a ng-click="remove()">{t _remove}</a></li>
		</ul>
	</div>

	<div class="treeManagerContainer">

		<tabset>
			<tab ng-repeat="instance in pages" ng-click="selectID(instance.ID)" heading="{{getTabTitle(instance)}}">
				<div ng-show="instance.ID">
					<ul class="menu" id="staticPageMenu">
						<li id="codeMenu">
							<a class="menu" ng-click="showcode = !showcode">{t _show_template_code}</a>
						</li>
					</ul>

					<fieldset id="templateCode" ng-show="showcode">
						<legend>{t _template_code}</legend>
						{t _code_explain}:
						<br /><br />
						&lt;a href="<strong>{ldelim}pageUrl id={{instance.ID}}{rdelim}</strong>"&gt;<strong>{ldelim}pageName id={{instance.ID}}{rdelim}</strong>&lt;/a&gt;
					</fieldset>
				</div>

				{form model="instance" name="form" rel="controller=backend.staticPage action=save" ng_submit="save(this)" handle=$form method="post" role="page.update(edit),page.create(add)"}

				<div id="editContainer">

					{input name="title"}
						{label}{t _title}:{/label}
						{if $page.ID}
							{textfield class="wider" id="title_`$page.ID`"}
						{else}
							{textfield class="wider" id="title_`$page.ID`" onkeyup="$('handle').value = ActiveForm.prototype.generateHandle(this.value);"}
						{/if}
					{/input}

					<p>{t _add_page_to_menu}</p>

					{input name="menuInformation"}
						{checkbox}
						{label}{t _information_menu}{/label}
					{/input}

					{input name="menuRootCategories"}
						{checkbox}
						{label}{t _main_header_menu}{/label}
					{/input}

					{input name="handle"}
						{label}{t _handle}:{/label}
						{textfield id="handle"}
					{/input}

					{input name="text"}
						{label class="wide"}{t _text}:{/label}
						<div class="textarea" id="textContainer">
							{textarea tinymce=true class="tinyMCE longDescr" style="width: 100%;"}
						</div>
					{/input}

					{input name="metaDescription"}
						{label class="wide"}{t _meta_description}:{/label}
						{textarea style="width: 100%; height: 4em;"}
					{/input}

					{include file="backend/eav/fields.tpl" item=$page angular="instance"}

					{language}
						{input name="title_`$lang.ID`"}
							{label}{t _title}:{/label}
							{textfield class="wider"}
						{/input}

						{input name="text_`$lang.ID`"}
							{label class="wide"}{t _text}:{/label}
							{textarea tinymce=true class="tinyMCE longDescr" style="width: 100%;"}
						{/input}

						{input name="metaDescription_`[[lang.ID]]`"}
							{label class="wide"}{t _meta_description}:{/label}
							{textarea style="width: 100%; height: 4em;"}
						{/input}

						{include file="backend/eav/fields.tpl" angular="instance" item=$page language=$lang.ID}
					{/language}

				</div>

				<fieldset class="controls">
					<span class="progressIndicator" id="saveIndicator" style="display: none;"></span>
					<input ng-disabled="form.$invalid" type="submit" value="{tn _save}" class="submit" />
					{t _or}
					<a class="cancel" id="cancel" onclick="return false;" href="#">{t _cancel}</a>
				</fieldset>

				{/form}
			</tab>
		</tabset>
	</div>
</div>

{include file="layout/backend/footer.tpl"}