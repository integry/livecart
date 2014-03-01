<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 *
 * @package test.model.category
 * @author Integry Systems
 */
class ProductControllerTest extends LiveCartTest implements ControllerTestCase
{
	/**
	 * Root category
	 * @var Category
	 */
	private $controller;

	private $product;

	public function getUsedSchemas()
	{
		return array(
			'CustomerOrder',
			'OrderedItem',
			'Shipment',
			'Product',
			'ProductRating',
			'ProductRatingType',
			'ProductReview',
			'User',
		);
	}

	public function setUp()
	{
		parent::setUp();
		ActiveRecordModel::executeUpdate('DELETE FROM ProductRatingType');

		$this->controller = new ProductController(self::getApplication());

		$this->product = Product::getNewInstance(Category::getRootNode());
		$this->product->isEnabled->set(true);
		$this->product->save();

		$this->request->set('id', $this->product->getID());

		$this->getConfig()->set('ENABLE_REVIEWS', true);
		$this->getConfig()->set('ENABLE_ANONYMOUS_RATINGS', true);
		$this->getConfig()->set('REVIEWS_WITH_RATINGS', false);
	}

	public function testSimpleRating()
	{
		$this->request->set('rating_', 4);
		$response = $this->controller->rate();

		$this->product->reload();
		$this->assertEqual($this->product->rating, 4);
		$this->assertIsA($response, 'ActionRedirectResponse');

		$this->request->set('ajax', 'true');
		$response = $this->controller->rate();
		$this->assertIsA($response, 'JSONResponse');
	}

	public function testRatingTypes()
	{
		$types = array();
		for ($k = 1; $k <= 3; $k++)
		{
			$type = ProductRatingType::getNewInstance(Category::getRootNode());
			$type->save();
			$types[$k] = $type;

			$this->request->set('rating_' . $type->getID(), $k);
		}

		$this->controller->rate();

		$this->product->reload();

		$response = $this->controller->index();
		$ratings = $response->get('rating');

 		$this->assertEqual(count($ratings), 3);

		foreach ($ratings as $key => $rating)
		{
			$this->assertEqual($key + 1, $rating['rating']);
		}
	}

	public function testAnonymousReviews()
	{
		$this->getConfig()->set('ENABLE_REVIEWS', true);
		$this->getConfig()->set('ENABLE_ANONYMOUS_RATINGS', true);

		$this->request->set('rating_', 4);
		$this->request->set('nickname', 'tester');
		$this->request->set('text', 'review');
		$this->request->set('ajax', 'true');
		$response = $this->controller->rate();

		$data = $response->getValue();
		$this->assertTrue(!empty($data['errors']));

		$this->request->set('title', 'some title');
		$response = $this->controller->rate();

		$this->product->reload();
		$this->assertEqual($this->product->rating, 4);
		$this->assertIsA($response, 'JSONResponse');

		$this->assertEqual($this->product->getRelatedRecordSet('ProductReview', new ARSelectFilter())->size(), 1);
	}

	public function testNonAnonymousReviews()
	{
		$this->getConfig()->set('ENABLE_REVIEWS', true);
		$this->getConfig()->set('ENABLE_ANONYMOUS_RATINGS', false);
		$this->request->set('rating_', 4);
		$this->request->set('ajax', 'true');

		$this->assertRatingError();

		// rate as user
		$user = User::getNewInstance('i-want-to-rate@example.com');
		$user->save();
		$this->controller->setUser($user);

		$this->assertRatingError(false);
	}

	public function testMustHavePurchasedToRate()
	{
		$user = User::getNewInstance('i-want-to-rate@example.com');
		$user->save();

		$this->getConfig()->set('ENABLE_REVIEWS', true);
		$this->getConfig()->set('ENABLE_ANONYMOUS_RATINGS', false);
		$this->getConfig()->set('REQUIRE_PURCHASE_TO_RATE', true);

		$this->request->set('rating_', 4);
		$this->request->set('ajax', 'true');

		$this->assertRatingError();

		$this->controller->setUser($user);

		// login not enough - should still fail
		$this->assertRatingError();

		ActiveRecordModel::executeUpdate('DELETE FROM Currency');
		$usd = Currency::getNewInstance('USD');
		$usd->save();

		$order = CustomerOrder::getNewInstance($user);
		$order->addProduct($this->product);
		$order->save();

		// order not completed - should still fail
		$this->assertRatingError();

		// order finalized - now works
		$order->finalize();
		$id = $this->product->getID();
		$this->setUp();
		$this->controller->setUser($user);
		$this->request->set('id', $id);

		$this->assertRatingError(false);
	}

	public function testReviewRequiredWithRating()
	{
		$this->getConfig()->set('ENABLE_ANONYMOUS_RATINGS', true);
		$this->getConfig()->set('REQUIRE_PURCHASE_TO_RATE', false);
		$this->getConfig()->set('REVIEWS_WITH_RATINGS', true);

		$this->request->set('rating_', 4);
		$this->request->set('ajax', 'true');

		// no review entered - error deserved
		$this->assertRatingError();

		// review entered
		$this->request->set('nickname', 'tester');
		$this->request->set('text', 'review');
		$this->request->set('title', 'summary');
		$this->request->set('ajax', 'true');
		$this->assertRatingError(false);
	}

	public function testReviewCounts()
	{
		$this->request->set('rating_', 4);
		$this->request->set('ajax', 'true');

		// review entered
		$this->request->set('nickname', 'tester');
		$this->request->set('text', 'review');
		$this->request->set('title', 'summary');
		$this->request->set('ajax', 'true');
		$this->assertRatingError(false);

		// review count
		$this->product->reload();
		$this->assertEqual($this->product->reviewCount, 1);

		// delete review
		$reviews = $this->product->getRelatedRecordSet('ProductReview', new ARSelectFilter());
		$this->assertEqual($reviews->size(), 1);

		$reviews->get(0)->delete();
		$this->product->reload();
		$this->assertEqual($this->product->reviewCount, 0);
	}

	public function testDoubleRatingsByUser()
	{
		$this->getConfig()->set('REVIEWS_WITH_RATINGS', false);

		$user = User::getNewInstance('i-want-to-rate@example.com');
		$user->save();
		$this->controller->setUser($user);

		$this->request->set('rating_', 4);
		$this->request->set('ajax', 'true');

		// first rating
		$response = $this->controller->rate();

		// attempt to rate twice
		$this->assertRatingError();

		// create a different user to rate
		$user = User::getNewInstance('i-want-to-rate-also@example.com');
		$user->save();
		$this->controller->setUser($user);
		$this->assertRatingError(false);
	}

	public function testDoubleRatingsByIP()
	{
		$this->getConfig()->set('REVIEWS_WITH_RATINGS', false);
		$this->getConfig()->set('RATING_SAME_IP_TIME', 24);

		$this->request->set('rating_', 4);
		$this->request->set('ajax', 'true');

		// first rating
		$this->assertRatingError(false);

		// attempt to rate twice
		$this->assertRatingError();

		// different IP
		$_SERVER['REMOTE_ADDR'] = '192.168.0.1';
		$this->assertRatingError(false);
	}

	public function testDoubleRatingsByIPWithTimeDifference()
	{
		$_SERVER['REMOTE_ADDR'] = '255.255.255.254';

		$this->getConfig()->set('RATING_SAME_IP_TIME', 24);

		$this->request->set('rating_', 4);
		$this->request->set('ajax', 'true');

		// first rating
		$this->assertRatingError(false);
		ActiveRecordModel::executeUpdate('UPDATE ProductRating SET dateCreated="1997-01-01"');

		$this->assertRatingError(false);

		// vote again - get error
		$this->assertRatingError();

		// no time difference - vote as much as you want
		$this->getConfig()->set('RATING_SAME_IP_TIME', 0);
		$this->assertRatingError(false);
	}

	public function testReviewApproval()
	{
		$config = $this->getConfig();
		$config->set('RATING_SAME_IP_TIME', 0);

		$this->request->set('rating_', 4);
		$this->request->set('ajax', 'true');
		$this->request->set('nickname', 'tester');
		$this->request->set('text', 'review');
		$this->request->set('title', 'summary');

		// no automatic approval
		$config->set('APPROVE_REVIEWS', 'APPROVE_REVIEWS_NONE');
		$this->controller->rate();
		$this->assertEqual($this->getReviewCount(), 0);

		$config->set('APPROVE_REVIEWS', 'APPROVE_REVIEWS_USER');
		$this->controller->rate();
		$this->assertEqual($this->getReviewCount(), 0);

		// auto approve for registered users only
		$config->set('APPROVE_REVIEWS', 'APPROVE_REVIEWS_USER');
		$user = User::getNewInstance('i-want-to-rate-also@example.com');
		$user->save();
		$this->controller->setUser($user);
		$this->controller->rate();
		$this->assertEqual($this->getReviewCount(), 1);
	}

	public function testReviewAutoApproval()
	{
		$this->request->set('rating_', 4);
		$this->request->set('nickname', 'tester');
		$this->request->set('text', 'review');
		$this->request->set('title', 'summary');
		$this->request->set('ajax', 'true');

		$this->getConfig()->set('APPROVE_REVIEWS', 'APPROVE_REVIEWS_AUTO');
		$this->controller->rate();
		$this->assertEqual($this->getReviewCount(), 1);
	}

	private function assertRatingError($hasError = true)
	{
		$data = $this->controller->rate()->getValue();
		$this->assertTrue(!empty($data['errors']) == $hasError);
	}

	private function getReviewCount()
	{
		$reviews = $this->controller->index()->get('reviews');
		return is_array($reviews) ? count($reviews) : 0;
	}
}

?>