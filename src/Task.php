<?php

namespace Serg\TaskApi;

class Task
{
    //public $title;
    public static function parseTaskFormat($json):bool
    {
        if (empty($json->title) || !is_string($json->title)) {
            return false;
        } /*else {
            $this->title = $json->title;
        }*/
        return true;
    }
}