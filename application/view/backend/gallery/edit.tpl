<dialog fullHeight=true class="gallery-edit" cancel="cancel()">
	<dialog-header>
		<span ng-show="vals.ID">{{vals.name}}</span>
		<span ng-show="!vals.ID">{t _add_gallery}</span>
	</dialog-header>
	<dialog-body>
		<tabset-lazy ng-class="{'hideTabs' : !vals.ID}">
			<tab-lazy class="main" title="{t _gallery_details}" template-url="[[ url('backend/gallery/basicData') ]]"></tab-lazy>
			<tab-lazy class="images" title="{t _images}" template-url="[[ url('backend/gallery/editImages') ]]"></tab-lazy>
		</tabset-lazy>
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<submit tabform="main">{t _save_gallery_details}</submit>
	</dialog-footer>
</dialog>
