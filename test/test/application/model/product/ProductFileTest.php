<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 *  @author Integry Systems
 *  @package test.model.product
 */
class ProductFileTest extends LiveCartTest
{
	/**
	 * @var Product
	 */
	private $product = null;

	/**
	 * @var Category
	 */
	private $rootCategory = null;

	/**
	 * @var ProductFileGroup
	 */
	private $group = null;

	/**
	 * @var ProductFile
	 */
	private $file = null;
	private $tmpFilePath = 'somefile.txt';
	private $fileBody = 'All your base are belong to us';

	public function __construct()
	{
		parent::__construct('Product files tests');

		$this->tmpFilePath = $this->config->getPath('cache/') . 'somefile.txt';

		$this->rootCategory = Category::getInstanceByID(Category::ROOT_ID);
	}

	public function getUsedSchemas()
	{
		return array(
			'ProductFile',
			'Product',
			'ProductFileGroup'
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->product = Product::getNewInstance($this->rootCategory, 'test');
		$this->product->save();

		$this->group = ProductFileGroup::getNewInstance($this->product);
		$this->group->save();

		// create temporary file
		file_put_contents($this->tmpFilePath, $this->fileBody);
	}

	public function testUploadNewFile()
	{
		// create
		$fileName = 'some_file';
		$extension = 'txt';

		$productFile = ProductFile::getNewInstance($this->product, $this->tmpFilePath, $fileName . '.' . $extension);

		$productFile->save();

		$productFile->reload();

		$this->assertEqual($productFile->fileName, 'some_file');
		$this->assertEqual($productFile->extension, $extension);
		$this->assertEqual($productFile->getPath(), $this->config->getPath('storage/productfile') . DIRECTORY_SEPARATOR . $productFile->getID());

		$productFile->delete();
	}

   	public function testDeleteFile()
   	{
   		$productFile = ProductFile::getNewInstance($this->product, $this->tmpFilePath, 'some_file.txt');
   		$productFile->save();
   		$productFilePath = $productFile->getPath();
   		$productFile->delete();

		$this->assertFalse(is_file($productFilePath));
   	}

   	public function testGetProductFiles()
   	{
		$productFiles = array();
		$productFilesO = array();
		foreach(range(1, 2) as $i)
		{
			file_put_contents($productFiles[$i] = $this->config->getPath('cache/') . md5($i), $this->fileBody);
			$productFilesO[$i] = ProductFile::getNewInstance($this->product, $productFiles[$i], 'test_file.txt');
			$productFilesO[$i]->save();
		}

   		$this->assertEqual(ProductFile::getFilesByProduct($this->product)->getTotalRecordCount(), 2);

   		foreach($productFiles as $file) unlink($file);
   		foreach($productFilesO as $pFile) $pFile->delete();
   	}

   	public function testChangeUploadedFile()
   	{
		$productFile = ProductFile::getNewInstance($this->product, $this->tmpFilePath, 'some_file.txt');
		$productFile->save();

		$this->assertEqual(file_get_contents($productFile->getPath()), $this->fileBody);

   		$reuploadedFile = $this->config->getPath('cache/') . 'reuploaded_file.txt';
		file_put_contents($reuploadedFile, $reuploadedFileBody = 'Reupload file');
		$productFile->storeFile($reuploadedFile, 'some_file.txt');
		$productFile->save();

		$this->assertEqual(file_get_contents($productFile->getPath()), $reuploadedFileBody);

		unlink($reuploadedFile);
		$productFile->delete();
   	}
}
?>