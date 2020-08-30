<?php

namespace Multifields\Base;

class FrontElements extends Front
{
    protected $disabled = false;

    /**
     * @param array $data
     * @param array $params
     * @return array
     */
    protected function afterFindData($data = [], &$params = [])
    {
        return $data;
    }
}