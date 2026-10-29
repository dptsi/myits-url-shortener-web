<?php

class IndexTest extends TestCase
{
    /**
     * Test Index
     *
     * @return void
     */
    public function testIndex() {
        // Guests are sent to myITS SSO login
        $this->get('/')
             ->assertRedirect(route('login'));
    }
}
