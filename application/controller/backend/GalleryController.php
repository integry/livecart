<?php

require_once(dirname(__FILE__) . '/abstract/ActiveGridController.php');

use Phalcon\Validation\Validator;

/**
 * Controller for handling gallery based actions performed by store administrators
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role gallery
 */
class GalleryController extends ActiveGridController// implements MassActionInterface
{
    public function indexAction()
	{


	}

	public function editAction()
	{

	}
	
	public function basicDataAction()
	{
		//$this->set('themes', array_merge(array(''), LiveCartRenderer::getThemeList()));

		$isExisting = true;
		$this->setValidator($this->buildValidator($isExisting));
	}

	public function getAction()
	{
		if ((int)$this->request->get('id'))
		{
			$gallery = Gallery::getInstanceByID($this->request->get('id'));
			$arr = $gallery->toArray();
		}
		else
		{
			$gallery = new Gallery();
			$arr = $gallery->toArray();
		}

		echo json_encode($arr);
	}
	
	public function eavAction()
	{
		return;
		if ((int)$this->request->get('id'))
		{
			$gallery = Gallery::getInstanceByID($this->request->get('id'));
		}
		else
		{
			$cat = Category::getInstanceByID($this->request->get('categoryID'), true);
			$gallery = new Gallery();
		}
		
		$manager = new \eav\EavFieldManager(\eav\EavField::getClassID($gallery));
		$manager->loadFields();
		
		echo json_encode($manager->toArray());
	}
	
	public function editCategoriesAction()
	{
	}

	public function editImagesAction()
	{
	}
	
	public function imagesAction()
	{
		if ($this->request->isPost())
		{
			$gallery = Gallery::getInstanceByID($this->request->getJson('id'));
			
			$images = array();
			foreach (\GalleryImage::query()->where('galleryID = :id:', array('id' => $gallery->getID()))->orderBy('position')->execute() as $img)
			{
				$images[$img->getID()] = $img;
			}
			
			foreach ($this->request->getJson('images') as $index => $image)
			{
				if (!empty($image['ID']))
				{
					$img = $images[$image['ID']];
					unset($images[$image['ID']]);
				}
				else
				{
					$img = \GalleryImage::getNewInstance($gallery);
				}
				
				$img->position = $index;
				$img->save();
				
				if (!empty($image['uploadedPath']))
				{
					$tmp = explode('/', $image['uploadedPath']);
					$path = __ROOT__ . '/public/upload/tmp/' . array_pop($tmp);
					$img->setFile($path);
				}
				
				if (0 == $index)
				{
					$gallery->defaultImageID = $img->getID();
				}
			}
			
			foreach ($images as $img)
			{
				$img->delete();
			}
		}

		if (empty($gallery))
		{
			$gallery = Gallery::getInstanceByID($this->request->get('id'));
		}
	
		$images = array();
		foreach (\GalleryImage::query()->where('galleryID = :id:', array('id' => $gallery->getID()))->orderBy('position')->execute() as $img)
		{
			$images[] = $img->toArray();
		}
		
		echo json_encode($images);
	}

	protected function getClassName()
	{
		return 'Gallery';
	}

	protected function getCSVFileName()
	{
		return 'gallerys.csv';
	}

	protected function getMassActionProcessor()
	{
		 		 return 'GalleryMassActionProcessor';
	}

	protected function getMassCompletionMessage()
	{
		return $this->translate('_mass_action_succeed');
	}

	protected function getDefaultColumns()
	{
		return array('Gallery.ID', 'Gallery.name');
	}

	/**
	 * Displays main gallery information form
	 *
	 * @role create
	 *
	 */
	public function addAction()
	{
		//$response = $this->galleryForm(false);
	}

	/**
	 * @role create
	 */
	public function createAction()
	{
		$gallery = Gallery::getNewInstance(Category::getInstanceByID($this->request->get('categoryID')), $this->translate('_new_gallery'));

		$response = $this->save($gallery);

		if ($response instanceOf ActionResponse)
		{
			$response->get('galleryForm')->clearData();
			$this->set('id', $gallery->getID());
		}
		else
		{
		}
	}

	public function updateAction()
	{
	  	$gallery = Gallery::getRequestInstance($this->request);
	  	
	  	if (!$gallery)
	  	{
	  		$gallery = new Gallery();
		}
	  	
	  	return $this->save($gallery);
	}

	private function save(Gallery $gallery)
	{
		$validator = $this->buildValidator(true);
		if (1 || $validator->isModelValid())
		{
			$gallery->loadRequestData($this->request);

			/*
			foreach (array('ShippingClass' => 'shippingClassID', 'TaxClass' => 'taxClassID') as $class => $field)
			{
				$value = $this->request->get($field, null, null);
				$instance = $value ? ActiveRecordModel::getInstanceByID($class, $value) : null;
				$gallery->writeAttribute($field, $instance);
			}
			*/

			$gallery->save();

			// save gallery images
			$inputImages = $this->request->get('galleryImage');
			$tmpImages = array();
			if (is_array($inputImages))
			{
				$dir = $this->config->getPath('public/upload/tmpimage/');
				foreach($inputImages as $tmpImage)
				{
					if (strlen(trim($tmpImage)) == 0 || strpos($tmpImage, '/'))
					{
						continue;
					}
					if(file_exists($dir.$tmpImage))
					{
						$tmpImages[] = $dir.$tmpImage;
						$galleryImage = GalleryImage::getNewInstance($gallery);
						$galleryImage->save();
						$galleryImage->setFile($dir.$tmpImage);
					}
				}
			}

			echo json_encode($gallery->toArray());exit;
		}
		else
		{
			// reset validator data (as we won't need to restore the form)
			$validator->restore();

			return new JSONResponse(array('errors' => $validator->getErrorList(), 'failure', $this->translate('_could_not_save_gallery_information')));
		}
	}

	/**
	 *
	 * @return \Phalcon\Validation
	 */
	public function buildValidator($isExisting)
	{
		$validator = $this->getValidator("galleryFormValidator", $this->request);
		//Gallery::setValidation($validator);

		$validator->add('name', new Validator\PresenceOf(array('message' => $this->translate('_err_name_empty'))));

		return $validator;
	}

	public function uploadGalleryImageAction()
	{
						$field = 'upload_' . $this->request->get('field');

		$dir = $this->config->getPath('public/upload/tmpimage/');

		// delete old tmp files
		chdir($dir);
		$dh = opendir($dir);
		$threshold = strtotime("-1 day");
		while (($dirent = readdir($dh)) != false)
		{
			if (is_file($dirent))
			{
				if (filemtime($dirent) < $threshold)
				{
					unlink($dirent);
				}
			}
		}
		closedir($dh);

		// create tmp file
		$file = $_FILES[$field];
		$tmp = 'tmp_' . $field . md5($file['tmp_name']) .  '__' . $file['name'];

		if (!file_exists($dir))
		{
			mkdir($dir, 0777, true);
			chmod($dir, 0777);
		}
		$path = $dir . $tmp;
		move_uploaded_file($file['tmp_name'], $path);
		if (@getimagesize($path))
		{
			$thumb = 'tmp_thumb_' . $tmp;
			$thumbPath = $dir . $thumb;
			$thumbDir = dirname($thumbPath);
			if (!file_exists($thumbDir))
			{
				mkdir($thumbDir, 0777, true);
				chmod($thumbDir, 0777);
			}
			$conf = $this->getConfig();
			$img = new ImageManipulator($path);
			$thumbSize = GalleryImage::getImageSizes();

			$thumbSize = $thumbSize[2]; // 1 is too small, cant see a thing.
			$img->resize($thumbSize[0], $thumbSize[1], $thumbPath);
			$thumb = 'upload/tmpimage/'. $thumb;
		}
		else
		{
			return new JSONResponse(null, 'failure', $this->translate('_error_uploading_image'));
		}
		return new JSONResponse(array('name' => $file['name'], 'file' => $tmp, 'thumb' => $thumb), 'success');
	}
}

?>
