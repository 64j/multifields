<?php

namespace Multifields\Elements\Table;

class Row extends \Multifields\Base\Elements
{
    protected $template = '
        <tr data-name="[+name+]">
            <th>
                <div class="mf-actions">
                    <i class="mf-actions-move fa"></i>
                    <i class="mf-actions-add fa" onclick="Multifields.elements.table.actionAddRow(event);"></i>
                </div>
            </th>
            [+items+]
            <th>
                <div class="mf-actions">
                    <i class="mf-actions-del fa" onclick="Multifields.elements.table.actionDelRow(event);"></i>
                </div>
            </th>
        </tr>';

    public function render()
    {
        return parent::view();
    }
}
