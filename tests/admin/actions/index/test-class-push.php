<?php

use \Actions\Index\Push as Push;
use \Exporter\Settings as Settings;
use \Prophecy\Argument as Argument;

class Admin_Action_Index_Push_Test extends WP_UnitTestCase {

	private $prophet;

	public function setup() {
		parent::setup();

		$this->prophet = new \Prophecy\Prophet;
		$this->settings = new Settings();
		$this->settings->set( 'api_key', 'foo' );
		$this->settings->set( 'api_secret', 'bar' );
		$this->settings->set( 'api_channel', 'baz' );
	}

	public function tearDown() {
		$this->prophet->checkPredictions();
	}

	protected function dummy_response() {
		$response = new stdClass;
		$response->data = new stdClass;
		$response->data->id = uniqid();
		$response->data->createdAt = time();
		$response->data->modifiedAt = time();
		$response->data->shareUrl = 'http://test.url/some-path';
		return $response;
	}

	public function testActionPerform() {
		$response = $this->dummy_response();
		$api = $this->prophet->prophesize( '\Push_API\API' );
		$api->post_article_to_channel( Argument::cetera() )
			->willReturn( $response )
			->shouldBeCalled();

		// Create post
		$post_id = $this->factory->post->create();

		$action = new Push( $this->settings, $post_id );
		$action->set_api( $api->reveal() );
		$action->perform();

		$this->assertEquals( $response->data->id, get_post_meta( $post_id, 'apple_export_api_id', true ) );
		$this->assertEquals( $response->data->createdAt, get_post_meta( $post_id, 'apple_export_api_created_at', true ) );
		$this->assertEquals( $response->data->modifiedAt, get_post_meta( $post_id, 'apple_export_api_modified_at', true ) );
		$this->assertEquals( $response->data->shareUrl, get_post_meta( $post_id, 'apple_export_api_share_url', true ) );
		$this->assertEquals( null, get_post_meta( $post_id, 'apple_export_api_deleted', true ) );
	}

}
