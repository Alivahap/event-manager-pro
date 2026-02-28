<?php

class Test_EMP_Event_Meta extends WP_UnitTestCase {

    public function test_event_meta_saved_and_retrieved() {

        $event_id = self::factory()->post->create([
            'post_type' => 'event',
            'post_status' => 'publish',
        ]);

        update_post_meta($event_id, '_emp_event_date', '2026-02-27');
        update_post_meta($event_id, '_emp_location', 'Istanbul');

        $this->assertSame(
            '2026-02-27',
            get_post_meta($event_id, '_emp_event_date', true)
        );

        $this->assertSame(
            'Istanbul',
            get_post_meta($event_id, '_emp_location', true)
        );
    }
}