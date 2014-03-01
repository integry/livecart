<style>
	.fade.in { display: inline-block; } /* Angular UI Bootstrap has some glitches with Bootstrap 3 */
</style>
<div class="modal-dialog">
	<div class="modal-content">
		[[ form("", ["method": "POST", "ng-submit": "save()", "ng-init": "setEmptyValue()"]) ]]>
		<div class="modal-header">
			<button type="button" class="close" aria-hidden="true" ng-click="close(0)">&times;</button>
			<h3 ng-show="!vals.ID">{t _add_new_field}</h3>
			<h3 ng-show="vals.ID">{t _edit_field}</h3>
		</div>
		<div class="modal-body" ng-class="{'hideTabs': !isSelect()}">
			<tabset>
				<tab heading="{t _SpecField_main}">
					<div class="form-group">
						<label class="specField_form_type_label">{tip _SpecField_type}</label>

						<select ng-model="vals.type" ng-disabled="vals.ID">
							<optgroup label="{t _SpecField_text}">
								<option value="5">{t _SpecField_type_text_selector}</option>
								<option value="3">{t _SpecField_type_simple_text}</option>
							</optgroup>
							<optgroup label="{t _SpecField_numbers}">
								<option value="1">{t _SpecField_type_numbers_selector}</option>
								<option value="2">{t _SpecField_type_numbers}</option>
							</optgroup>
							<option value="6">{t _SpecField_type_date}</option>
						</select>
					</div>
					
					<div ng-show="isSelect()">
						[[ checkbox('isMultiValue', '_SpecField_select_multiple') ]]
					</div>
					
					<div ng-show="vals.type == 3">
						[[ checkbox('advancedText', '_SpecField_formated_text') ]]
					</div>
					
					[[ textfld('name', '_SpecField_title') ]]
					
					[[ textfld('handle', '_SpecField_handle') ]]
					
					<div ng-show="isNumber()">
						[[ textfld('valuePrefix', '_SpecField_valuePrefix') ]]
						[[ textfld('valueSuffix', '_SpecField_valueSuffix') ]]
					</div>
					
					[[ textfld('description', '_SpecField_description') ]]
					
					[[ checkbox('isRequired', tip('_SpecField_is_required')) ]]
					[[ checkbox('isDisplayed', tip('_SpecField_displayed_on_front_page')) ]]
					[[ checkbox('isDisplayedInList', tip('_SpecField_displayed_in_product_list')) ]]
					[[ checkbox('isSortable', tip('_SpecField_sortable')) ]]
				</tab>
				<tab heading="{t _SpecField_values}">
					
					<div class="btn-toolbar">
						<div class="btn-group">
							<a class="btn btn-primary" ng-click="sortAZ()">A-Z</a>
						</div>
					</div>
					
					<div ui-sortable ng-model="vals.values">
					<div ng-repeat="value in vals.values">
						<div class="form-group">
							<drag-icon></drag-icon>
							<input type="text" class="form-control" ng-change="addRemoveValues()" ng-model="value.value" />
						</div>
					</div>
					</div>
				</tab>
			</tabset>
		</div>
		<div class="modal-footer">
			<button class="btn btn-warning cancel" ng-click="close(0)">{t _cancel}</button>
			<submit>{t _save}</submit>
		</div>
		</form>
	</div>
</div>
