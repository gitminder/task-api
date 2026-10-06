<?php

namespace Serg\TaskApi;

class TaskService
{
    public function getTasks(): string{
        $storage = new Storage();
        return json_encode($storage->getAll());
    }

    /**
     * @throws \Exception
     */
    //public function addTask(?string $rawBody = null): object{
    public function addTask($raw): object{
        //$raw = $rawBody ?? file_get_contents('php://input');
        $data = json_decode($raw, false);
        if (json_last_error() !== JSON_ERROR_NONE) {
            //$error = true;
            throw new \Exception(json_last_error_msg());
        } else {

            if (!Task::parseTaskFormat($data)){
                //$error = true;
                throw new \Exception('Invalid task format');
            } else {
                $storage = new Storage();
                $result = $storage->add($data);
                $storage->save();
                return $result;
            }
        }
    }
}