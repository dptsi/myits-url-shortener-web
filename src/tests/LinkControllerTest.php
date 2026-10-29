<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

class LinkControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test LinkController
     *
     * @return void
     */
    public function testRequestGetNotExistShortUrl() {
        $this->get('/notexist')
             ->assertOk()
             ->assertViewIs('errors.404');
    }
}
