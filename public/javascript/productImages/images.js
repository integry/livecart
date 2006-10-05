
var productImages = new ProductImages();

/**
 */
function ProductImages() {
  
  	this.imagesLayers = new Array();
  	this.deleteLink = '';  	
  	
  	this.editedTitleID = 0;
  	this.editedTitleValue = '';
  	
  	this.editedPictureID = 0;
}

ProductImages.prototype.preloadImage = function(imgSrc) {
  
  	var image = new Image;
  	image.src = imgSrc;  	
}

/**
 */
ProductImages.prototype.addImage = function(id, content) { //optional args

	allLayer = docHelper.getLayer("imagesLayer");		
		
	//this.imagesLayers[id] = document.createElement("div");
	//this.imagesLayers[id].innerHTML = content;	
	layer = document.createElement("div");
	layer.id = "imageLayer" + id;
	layer.innerHTML = content;
	        
	allLayer.appendChild(layer);		
    //allLayer.appendChild(this.imagesLayers[id]);		
}

/**
 */
ProductImages.prototype.changeImage = function(oldId, newId, content) { //optional args

	this.editedPictureID = 0;

	layer = docHelper.getLayer("imageLayer" + oldId);			
	layer.id = "imageLayer" + newId;
		
	layer = docHelper.getLayer("imageLayer" + newId);	
	layer.innerHTML = content;	    
}

/**
 */
ProductImages.prototype.editTitle = function(id, title) {
  	
  	this.cancelTitle();
  	this.editedTitleID = id;
  	
  	layer = docHelper.getLayer("titleSpan" + id);
  	this.editedTitleValue = layer.innerHTML;  	
  	
  	layer = docHelper.getLayer("titleSpan" + id);	
  	layer.innerHTML = "<br><input name='title" + id + "' id='title" + id + "'  type='text' style='width: 200px' value='" + title + "'><input type='button' value='Save' onclick='productImages.updateTitle(" + id +")'><input type='button' value='Cancel' onclick='productImages.cancelTitle();'><br>";  	
}

/**
 */
ProductImages.prototype.cancelTitle = function() {
  
  	if (this.editedTitleID != 0) {
	  	
		layer = docHelper.getLayer("titleSpan" + this.editedTitleID);
  		layer.innerHTML = this.editedTitleValue;  	
  	
  		this.editedTitleID = 0;
	  	this.editedTitleValue = '';
	}
}

/**
 */
ProductImages.prototype.editPicture = function(productID, id) {
  	
  	this.cancelPicture();
  	this.editedPictureID = id;
  	
  	layer = docHelper.getLayer("imageSpan" + id);
  	layer.innerHTML = "<form target='iframeUpload' action='/k-shop/public/index.php?/backend.product/saveImage/" + productID + "' enctype='multipart/form-data' onsubmit='if (validateForm(this)) {popWait(); return true;} else {return false;};return validateForm(this);' method='POST' name='saveImage'><input name='imageFile' validate='{&quot;RequiredValueCheck&quot;:{&quot;err&quot;:&quot;Image file is not defined!&quot;,&quot;constraint&quot;:[]},&quot;UploadImageCheck&quot;:{&quot;err&quot;:&quot;Not correct image file!&quot;,&quot;constraint&quot;:[]}}' type='file' style='' value=''><input type='hidden' name='imageID' value='" + id + "'><input type='submit' value='Save'><input type='button' value='Cancel' onclick='productImages.cancelPicture();'></form>";  
}

/**
 */
ProductImages.prototype.cancelPicture = function(id) {

  	if (this.editedPictureID != 0) {
	    
		layer = docHelper.getLayer("imageSpan" + this.editedPictureID);
  		layer.innerHTML = '';  	 
  		
  		this.editedPictureID = 0;
	}
}

/**
 */
ProductImages.prototype.updateTitle = function(id) {
  	
  	var titleInput = docHelper.getLayer('title' + id);  
		
	post = new Array();		
	post.imageID = id;
	post.title = titleInput.value;	
	http('POST', this.descriptionLink, this.responseSave, post, false);	  	  	
}

/**
 */
ProductImages.prototype.responseSave = function(data) {
	  
	if (data.title) {
	  
		layer = docHelper.getLayer("titleSpan" + data.id);
  		layer.innerHTML = data.title + '<br>';  	  
	}
}

/**
 */
ProductImages.prototype.deleteImage = function(id) {
		
	post = new Array();		
	post.imageId = id;
	http('POST', this.deleteLink, this.responseDelete, post, false);	
}

/**
 */	
ProductImages.prototype.responseDelete = function(data) {
	  
	if (data.deletedID) {
	  
		productImages.removeImageLayer(data.deletedID);	
	}
}

ProductImages.prototype.removeImageLayer = function(id) {

	/*if (this.imagesLayers[id]) {
		 
		docHelper.deleteLayer(this.imagesLayers[id]);   
	} else {	*/

		layer = docHelper.getLayer('imageLayer' + id);		
		docHelper.deleteLayer(layer);
	//}
}











