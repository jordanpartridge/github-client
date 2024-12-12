<?php

namespace Tests\Unit\DTOs\Workflows;

use Tests\TestCase;

class WorkflowDTOTest extends TestCase
{
    /** @test */
    public function it_can_create_workflow_dto_from_array()
    {
        $data = [
            'id' => 1,
            'name' => 'Test Workflow',
            'path' => '.github/workflows/test.yml',
            'state' => 'active',
        ];

        $this->markTestIncomplete('Implementation pending');
    }
}
