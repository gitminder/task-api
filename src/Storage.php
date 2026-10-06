<?php

namespace Serg\TaskApi;

class Storage
{
    const STORAGE_FILE_NAME = __DIR__.'/../storage/tasks.json';
    private $storage;
    public function __construct()
    {
        $this->load();
    }
    private function load()
    {
        $fContent = file_get_contents(self::STORAGE_FILE_NAME);
        $this->storage = json_decode($fContent);
    }
    public function save(){
        file_put_contents(self::STORAGE_FILE_NAME, json_encode($this->storage));
    }
    public function add($item){
        $maxId = 1;
        foreach($this->storage as $element){
            if($element->id > $maxId){
                $maxId = $element->id;
            }
        }
        $item->id = $maxId + 1;
        $item->created_at = time();
        $this->storage[] = $item;
    }
    public function getAll()
    {
        return $this->storage;
    }
}