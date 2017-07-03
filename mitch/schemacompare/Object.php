<?php

namespace mitch\schemacompare;

class Object
{
    public function __construct($options = [])
    {
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
    }
}