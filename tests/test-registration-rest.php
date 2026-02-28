<?php

class Test_EMP_Registration_REST extends WP_UnitTestCase {

    public function test_register_endpoint_rejects_invalid_event() {

        $request = new WP_REST_Request(
            'POST',
            '/event-manager-pro/v1/events/999999/register'
        );

        $request->set_param('name', 'Ali');
        $request->set_param('email', 'ali@example.com');

        $response = rest_do_request($request);

        $this->assertSame(400, $response->get_status());
    }

    public function test_register_endpoint_accepts_valid_payload() {

        $event_id = self::factory()->post->create([
            'post_type' => 'event',
            'post_status' => 'publish',
        ]);

        $request = new WP_REST_Request(
            'POST',
            '/event-manager-pro/v1/events/'.$event_id.'/register'
        );

        $request->set_param('name', 'Ali');
        $request->set_param('email', 'ali@example.com');

        $response = rest_do_request($request);

        $this->assertSame(200, $response->get_status());

        $data = $response->get_data();
        $this->assertTrue($data['success']);
    }

    public function test_register_endpoint_prevents_duplicates() {

        $event_id = self::factory()->post->create([
            'post_type' => 'event',
            'post_status' => 'publish',
        ]);

        $r1 = new WP_REST_Request(
            'POST',
            '/event-manager-pro/v1/events/'.$event_id.'/register'
        );

        $r1->set_param('name','Ali');
        $r1->set_param('email','ali@example.com');
        rest_do_request($r1);

        $r2 = new WP_REST_Request(
            'POST',
            '/event-manager-pro/v1/events/'.$event_id.'/register'
        );

        $r2->set_param('name','Ali');
        $r2->set_param('email','ali@example.com');

        $resp2 = rest_do_request($r2);

        $this->assertSame(409, $resp2->get_status());
    }
}