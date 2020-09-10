<?php

namespace Multifields\Elements\Table;

class Td extends \Multifields\Base\Elements
{
    protected $template = '<td data-type="[+@type+]" data-name="[+name+]">[+items+]</td>';

    public function render()
    {
        return parent::view();
    }

    /**
     * @param array $item
     * @param array $config
     * @param array $find
     */
    protected function preFillData(&$item = [], $config = [], $find = [])
    {
        $item['@type'] = '';
        if (!empty($item['items'])) {
            foreach ($item['items'] as $k => $v) {
                $type = isset($v['type']) ? $v['type'] : (isset($find['items'][$k]['type']) ? $find['items'][$k]['type'] : null);
                if (!empty($type)) {
                    $item['@type'] = $type;
                }
            }
        }
    }
}
