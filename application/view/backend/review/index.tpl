<div class="reviewGrid" id="reviewGrid_[[id]]" class="maxHeight h--50">

	[[ partial("backend/review/grid.tpl") ]]

</div>

{* Editors *}
<div id="reviewManagerContainer_template" class="dom_template treeManagerContainer" style="display: none;">
	<fieldset class="container">
		<ul class="menu">
			<li class="done"><a href="#cancelEditing" id="cancelReviewEdit_[[id]]" class="cancel">{t _cancel_editing_review}</a></li>
		</ul>
	</fieldset>

	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabReviewEdit" class="tab active">
				<a href="{link controller="backend.review" action=edit id=_id_}"}">...</a>
				<span class="tabHelp">products</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>

	<script type="text/javascript">
		Event.observe("cancelReviewEdit_[[id]]"	{literal}, "click", function(e) {
			e.preventDefault();
			var editor = Backend.Review.Editor.prototype.getInstance(Backend.Review.Editor.prototype.getCurrentId(), false);
			editor.cancelForm();
		});
	</script>
	{/literal}
</div>

{literal}
<script>
	if (!$('reviewManagerContainer'))
	{
		var container = $('reviewManagerContainer_template');
		container.id = 'reviewManagerContainer';
		container.removeClassName('dom_template');

		$('managerContainer').parentNode.insertBefore($('reviewManagerContainer'), $('managerContainer'));
	}
</script>
{/literal}