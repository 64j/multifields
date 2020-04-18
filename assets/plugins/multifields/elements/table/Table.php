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
        <div id="[+id+]" class="mf-table row [+class+]" data-type="table" data-name="[+name+]" [+attr+]>
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
            <div class="mf-items mf-items-table row [+items.class+]">
                <div class="position-relative w-100 m-0 p-0">
                    <div class="col-resize"></div>
                    <table class="table table-sm data table-hover table-bordered mb-3">
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
                <input type="text" class="form-control form-control-sm" name="' . $params['id'] . '_value" value="' . stripcslashes($params['value']) . '"' . (isset($params['placeholder']) ? ' placeholder="' . $params['placeholder'] . '"' : '') . ' data-value>
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
     * @param $params
     */
    protected function thead(&$params)
    {
        if (empty($params['items']) && !empty($params['thead'])) {
            $cells = '';

            foreach ($params['thead'] as $k => $v) {
                $type = isset($v['type']) ? $v['type'] : 'text';
                $v['type'] = isset($v['type']) && $v['type'] == 'id' ? 'id' : 'text';
                $cells .= parent::element('table:th')
                    ->render([
                        '@type' => $type,
                        'items' => parent::renderFormElement($v)
                    ]);
            }

            $params['@items'] .= parent::element('table:head')
                ->render([
                    'items' => parent::element('table:row')
                        ->render([
                            'items' => $cells
                        ])
                ]);
        }
    }

    /**
     * @param $params
     */
    protected function tbody(&$params)
    {
        if (empty($params['items']) && !empty($params['tbody'])) {
            $cells = '';

            foreach ($params['tbody'] as $k => $v) {
                $cells .= parent::element('table:td')
                    ->render([
                        'items' => parent::renderFormElement($v)
                    ]);
            }

            $params['@items'] .= parent::element('table:body')
                ->render([
                    'items' => parent::element('table:row')
                        ->render([
                            'items' => $cells
                        ])
                ]);
        }
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

        $params['@items'] = '';

        $this->getValue($params);

        $this->menu($params);
        $this->thead($params);
        $this->tbody($params);

        if (empty($params['items']) && !empty($params['@items'])) {
            $params['items'] = $params['@items'];
            unset($params['@items']);
        }

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
