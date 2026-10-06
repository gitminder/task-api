<?php

namespace Serg\TaskApi\Tests;

use PHPUnit\Framework\TestCase;
use Serg\TaskApi\TaskService;

class TaskServiceTest extends TestCase
{
    private string $storageFile;
    private string $originalContent;

    protected function setUp(): void
    {
        $this->storageFile = __DIR__ . '/../storage/tasks.json';
        $this->originalContent = file_get_contents($this->storageFile);
        file_put_contents($this->storageFile, '[]');
    }

    protected function tearDown(): void
    {
        file_put_contents($this->storageFile, $this->originalContent);
    }

    public function testAddTaskReturnsObjectWithRequiredFields(): void
    {
        $service = new TaskService();
        $result = $service->addTask(json_encode(['title' => 'Buy milk']));

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('id', $result);
        $this->assertObjectHasProperty('title', $result);
        $this->assertObjectHasProperty('created_at', $result);
        $this->assertSame('Buy milk', $result->title);
    }
}
