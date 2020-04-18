<?php

namespace Multifields\Elements\Table;

class Th extends \Multifields\Base\Elements
{
    protected $template = '
        <th data-type="[+@type+]">
            <i class="mf-column-settings fas fa-angle-down" onclick="Multifields.elements.table.columnMenu(event)"></i>
            [+items+]
        </th>';

    protected function preFillData(&$item = [], $config = [], $find = [])
    {
        $item['@type'] = '';
        if (!empty($item['items'])) {
            foreach ($item['items'] as $k => &$v) {
                if (isset($v['type'])) {
                    $item['@type'] = $v['type'];
                    if ($v['type'] != 'id') {
                        $v['type'] = 'text';
                    }
                }
            }
        }
    }
}
