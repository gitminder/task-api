<?php

namespace Serg\TaskApi;

class Storage
{
    public const STORAGE_FILE_NAME = __DIR__.'/../storage/tasks.json';

    /** @var array<object> */
    private array $storage;
    public function __construct()
    {
        $this->load();
    }
    private function load(): void
    {
        $fContent = @file_get_contents(self::STORAGE_FILE_NAME);
        $this->storage = ($fContent === false) ? [] : json_decode($fContent);
    }
    public function save(): void
    {
        file_put_contents(self::STORAGE_FILE_NAME, json_encode($this->storage));
    }
    public function add(object $item): object
    {
        $maxId = 1;
        foreach ($this->storage as $element) {
            if ($element->id > $maxId) {
                $maxId = $element->id;
            }
        }
        $item->id = $maxId + 1;
        $item->created_at = time();
        $this->storage[] = $item;
        return $item;
    }

    /**
     * @return object[]
     */
    public function getAll(): array
    {
        return $this->storage;
    }
}
