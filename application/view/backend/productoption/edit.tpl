<dialog>
	<dialog-header>
		<h3 ng-show="!vals.ID">{t _add_new_field}</h3>
		<h3 ng-show="vals.ID">{{ vals.name }}</h3>
	</dialog-header>
	<dialog-body>
		[[ form("", ["method": "POST", "ng-submit": "save()", "ng-init": "setEmptyValue()"]) ]]>
		<div ng-class="{'hideTabs': !isSelect()}">
			<tabset>
				<tab heading="{t _ProductOption_main}">
					<div class="form-group">
						<label>{tip ProductOption.type}</label>

						<select ng-model="vals.type" ng-disabled="vals.ID">
							{# <option value="0">{t _ProductOption_type_bool}</option> #}
							<option value="1">{t _ProductOption_type_select}</option>
							{#
							<option value="2">{t _ProductOption_type_text}</option>
							<option value="3">{t _ProductOption_type_file}</option> #}
						</select>
					</div>
					
					[[ textfld('name', 'ProductOption.title') ]]
					
					<div ng-show="isSelect()">
						<div class="form-group">
							<label>{tip ProductOption.displayType}</label>
							<select ng-model="vals.displayType">
								<option value="0">{t _ProductOption_displayType_selectBox}</option>
								<option value="1">{t _ProductOption_displayType_radioButtons}</option>
								<option value="2">{t _ProductOption_displayType_color}</option>
							</select>
						</div>

						[[ textfld('selectMessage', 'ProductOption.selectMessage') ]]
					</div>
				
					[[ textfld('description', 'ProductOption.description') ]]
					
					[[ checkbox('isRequired', tip('ProductOption.isRequired')) ]]
					[[ checkbox('isDisplayed', tip('ProductOption.isDisplayed')) ]]
					[[ checkbox('isDisplayedInList', tip('ProductOption.isDisplayedInList')) ]]
					[[ checkbox('isDisplayedInCart', tip('ProductOption.isDisplayedInCart')) ]]
					
				</tab>
				<tab heading="{t _ProductOption_values}">
					
					<div class="btn-toolbar">
						<div class="btn-group">
							<a class="btn btn-primary" ng-click="sortAZ()">A-Z</a>
						</div>
					</div>
					
					<div ui-sortable ng-model="vals.choices">
					<div ng-repeat="value in vals.choices">
						<div class="form-group">
							<drag-icon></drag-icon>
							<div class="row">
								<div class="col-sm-8">
									<input type="text" class="form-control" ng-change="addRemoveValues()" ng-model="value.name" />
								</div>
								<div class="col-sm-4">
									<div class="row">
										<div class="col-sm-8">
											<input type="text" class="form-control" ng-model="value.priceDiff" placeholder="{t _option_price_diff}" />
										</div>
										<div class="col-sm-4">
											EUR
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					</div>
				</tab>
			</tabset>
		</div>
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<submit tabform="main">{t _save}</submit>
	</dialog-footer>
</dialog>
