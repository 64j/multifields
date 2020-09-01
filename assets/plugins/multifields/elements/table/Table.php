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
        <div id="[+id+]" class="mf-table col col-12 row m-0 [+class+]" data-type="table" data-name="[+name+]" [+attr+]>
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

    /**
     * @param $params
     */
    protected function getValue(&$params)
    {
        if (isset($params['value']) && $params['value'] !== false) {
            if (is_bool($params['value'])) {
                $params['value'] = '';
            }

            $params['value'] = '
            <div class="mf-value mf-text">
                <input type="text" class="form-control" name="' . $params['id'] . '_value" value="' . stripcslashes($params['value']) . '"' . (isset($params['placeholder']) ? ' placeholder="' . $params['placeholder'] . '"' : '') . ' data-value>
            </div>';
        }
    }

    /**
     * @param $params
     */
    protected function menu(&$params)
    {
        if (empty($params['types'])) {
            $params['types'] = $this->types;
        }

        foreach ($params['types'] as $k => &$v) {
            if (stripos($k, 'separator') !== false) {
                $v = '<div class="separator cntxMnuSeparator"></div>';
            } else {
                $v = '<div onclick="Multifields.elements.table.setType(event, \'' . $k . '\');" data-type="' . $k . '">' . $v . '</div>';
            }
        }

        $params['types'] = implode('', $params['types']);
    }

    /**
     * @param array $params
     * @param array $data
     * @return string
     */
    public function render($params = [], $data = [])
    {
        if (!isset($params['items'])) {
            $params['items'] = '';
        }

        if (!empty($params['title'])) {
            $params['title'] = '<div class="mf-title">' . $params['title'] . '</div>';
        } else {
            $params['title'] = '';
        }

        $this->getValue($params);
        $this->menu($params);

        return parent::render($params, $data);
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
