<div ng-controller="EavFieldController">

	<a class="btn btn-primary" ng-click="add()">{t _add_field}</a>
	
	<grid controller="backend/eavField" primaryKey="EavField_ID">
		<actions>
			<edit-button>{t _edit}</edit-button>
		</actions>
		<mass>
			{* include file="backend/product/massAction.tpl" *}
		</mass>
	</grid>

</div>
