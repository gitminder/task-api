<?php

namespace Serg\TaskApi\Tests;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Serg\TaskApi\TaskService;

class TaskServiceTest extends TestCase
{
    private const string STORAGE_PILE_PATH = __DIR__ . '/../storage/tasks.json';
    private string $storageFile;
    private string $originalContent;

    protected function setUp(): void
    {
        $this->storageFile = self::STORAGE_PILE_PATH;
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

    public function testDeleteTaskPersistsRemainingTasksAsArray(): void
    {
        $service = new TaskService();
        $first = $service->addTask('{"title":"First"}');
        $second = $service->addTask('{"title":"Second"}');

        $this->assertTrue($service->deleteTask($first->id));
        $tasks = json_decode($service->getTasks());
        $this->assertIsArray($tasks);
        $this->assertCount(1, $tasks);
        $this->assertSame($second->id, $tasks[0]->id);
        $this->assertFalse($service->deleteTask($first->id));
        $this->assertTrue($service->deleteTask($second->id));
        $this->assertSame('[]', $service->getTasks());
    }

    public function testDeleteMissingTaskLeavesStorageUnchanged(): void
    {
        $service = new TaskService();
        $service->addTask('{"title":"Keep"}');
        $before = file_get_contents($this->storageFile);

        $this->assertFalse($service->deleteTask(999));
        $this->assertSame($before, file_get_contents($this->storageFile));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDeleteEndpointReturnsNoContent(): void
    {
        $service = new TaskService();
        $task = $service->addTask('{"title":"Delete me"}');

        $this->assertSame('', $this->deleteRequest('/tasks/' . $task->id));
        $this->assertSame(204, http_response_code());
        $this->assertSame('[]', $service->getTasks());
        $this->assertSame('{"error":"Not Found"}', $this->deleteRequest('/tasks/' . $task->id));
        $this->assertSame(404, http_response_code());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDeleteEndpointRejectsInvalidPaths(): void
    {
        $service = new TaskService();
        $task = $service->addTask('{"title":"Keep"}');
        $before = $service->getTasks();

        foreach (['/tasks', '/tasks/abc', '/tasks/0', '/tasks/-1', '/tasks/1.5', '/tasks/999999999999999999999999', '/tasks/' . $task->id . '/extra'] as $path) {
            $this->assertSame('{"error":"Not Found"}', $this->deleteRequest($path));
            $this->assertSame(404, http_response_code());
            $this->assertSame($before, $service->getTasks());
        }
    }

    private function deleteRequest(string $path): string
    {
        $originalServer = $_SERVER;
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = $path;
        http_response_code(200);
        ob_start();
        try {
            require __DIR__ . '/../public/index.php';
            return ob_get_contents();
        } finally {
            ob_end_clean();
            $_SERVER = $originalServer;
        }
    }
}
