<?php

namespace Multifields\Elements\Table;

class Table extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/table.css';
    protected $scripts = 'view/js/table.js';
    protected $tpl = 'view/table.tpl';

    protected $actions = [
        'add',
        'move',
        'del',
    ];

    protected $types = [
        'separator' => '',
        'text' => '[+lang.type.text+]',
        'number' => '[+lang.type.number+]',
        'date' => '[+lang.type.date+]',
        'image' => '[+lang.type.image+]',
        'file' => '[+lang.type.file+]',
    ];

    protected $template = '
        <div id="[+id+]" class="mf-table col row m-0 [+class+]" data-type="table" data-name="[+name+]" [+attr+]>
            [+title+]
            [+value+]
            [+actions+]
            <div class="row m-0 col-12 p-0">
                <div class="mf-column-menu contextMenu">
                    <div onclick="Multifields.elements.table.addColumn(event);" data-action="addColumn">
                        <i class="fa fa-plus fa-fw"></i> [+lang.add_column+]
                    </div>
                    <div onclick="Multifields.elements.table.delColumn(event);" data-action="delColumn">
                        <i class="fa fa-minus fa-fw"></i> [+lang.del_column+]
                    </div>
                    [+types+]
                </div>
            </div>
            <div class="mf-items mf-items-table row m-0 col-12 p-0 [+items.class+]">
                <div class="position-relative w-100 m-0 p-0">
                    <div class="col-resize"></div>
                    <table class="table table-sm data table-hover table-bordered">
                        [+items+]
                    </table>
                </div>
            </div>
        </div>';

    protected function setMenu()
    {
        if (empty($this->params['types'])) {
            $this->params['types'] = $this->types;
        }

        foreach ($this->params['types'] as $k => &$v) {
            if (stripos($k, 'separator') !== false) {
                $v = '<div class="separator cntxMnuSeparator"></div>';
            } else {
                $v = '<div onclick="Multifields.elements.table.setType(event, \'' . $k . '\');" data-type="' . $k . '">' . $v . '</div>';
            }
        }

        $this->params['types'] = implode('', $this->params['types']);
    }

    /**
     * @return string
     */
    public function render()
    {
        if (!isset($this->params['items'])) {
            $this->params['items'] = '';
        }

        if (empty($this->params['class'])) {
            $this->params['class'] = 'col-12';
        }

        $this->setValue();
        $this->setMenu();
        $this->setActions();

        return parent::render();
    }

    /**
     * @param array $params
     * @return string
     */
    public function actionGetElementByType($params = [])
    {
        $params['html'] = $this->renderFormElement([
            'type' => $params['type'],
            'name' => $params['name'],
            'id' => $params['id'],
        ]);

        return json_encode($params, JSON_UNESCAPED_UNICODE);
    }
}
