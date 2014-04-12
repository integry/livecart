<dialog fullHeight="true" class="gallery-image">
	<dialog-body>
		<img ng-src="upload/galleryimage/{{ gallery }}-{{ image }}-original.jpg" ng-click="next()" class="img-responsive" />
	</dialog-body>
	<dialog-footer>
		<a class="btn btn-primary pull-left" ng-click="prev()"><span class="glyphicon glyphicon-chevron-left"></span> Atpakaļ</a>
		<a class="btn btn-primary pull-right" ng-click="next()">Tālāk <span class="glyphicon glyphicon-chevron-right"></span></a>
	</dialog-footer>
</dialog>
