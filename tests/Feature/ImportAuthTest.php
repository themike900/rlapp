<?php

namespace Tests\Feature;

use Tests\TestCase;

class ImportAuthTest extends TestCase
{
    public function test_guest_cannot_post_the_member_import(): void
    {
        $this->post('/import')->assertRedirect('/login');
    }
}
