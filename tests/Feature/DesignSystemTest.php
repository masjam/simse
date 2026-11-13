<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    public function test_design_system_page_is_accessible_in_non_production(): void
    {
        $response = $this->get('/dev/design-system');

        $response->assertStatus(200);
    }

    public function test_design_system_page_aborts_404_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $response = $this->get('/dev/design-system');

        $response->assertStatus(404);
    }
}
