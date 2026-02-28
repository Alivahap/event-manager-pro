<?php

class Test_EMP_Event_CPT extends WP_UnitTestCase {

    public function test_event_post_type_is_registered() {
        $pt = get_post_type_object('event');

        $this->assertNotNull($pt);
        $this->assertTrue($pt->public);
        $this->assertTrue($pt->show_in_rest);
        $this->assertTrue($pt->has_archive);
    }

    public function test_event_type_taxonomy_is_registered() {
        $tax = get_taxonomy('event_type');

        $this->assertNotNull($tax);
        $this->assertTrue($tax->public);
        $this->assertTrue($tax->show_in_rest);
    }
}