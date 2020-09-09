<?php

namespace Multifields\Elements\Table;

class Th extends \Multifields\Base\Elements
{
    protected $template = '
        <th data-type="[+@type+]" data-name="[+name+]">
            <i class="mf-column-settings fas fa-angle-down" onclick="Multifields.elements.table.columnMenu(event)"></i>
            [+items+]
        </th>';

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
            foreach ($item['items'] as $k => &$v) {
                $type = isset($v['type']) ? $v['type'] : (isset($find['items'][$k]['type']) ? $find['items'][$k]['type'] : null);
                if (!empty($type)) {
                    $item['@type'] = $type;
                    $v['type'] = $type;
                    if ($v['type'] != 'id') {
                        $v['type'] = 'text';
                    }
                }
            }
        }
    }
}
