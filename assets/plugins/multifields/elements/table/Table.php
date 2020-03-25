<?php

namespace Multifields\Elements;

use Multifields\Base\Core;

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
        'text' => 'Text',
        'number' => 'Number',
        'date' => 'Date',
        'image' => 'Image',
        'file' => 'File',
    ];

    /**
     * @param array $items
     * @return array|string
     */
    protected function _renderData($items = [])
    {
        return $items;
    }

    protected function preFillData(&$item = [], $config = [], $find = [])
    {
        if (!empty($item['columns'])) {
            foreach ($item['columns'] as $k => &$v) {
                if (isset($find['columns'][$v['name']]['attr'])) {
                    $v['attr'] = $find['columns'][$v['name']]['attr'];
                }
            }
        }

        if (!empty($item['items'])) {
            foreach ($item['items'] as $k => &$v) {
                if (!empty($v['items'])) {
                    foreach ($v['items'] as $key => &$val) {
                        if (isset($find['columns'][$val['name']]['attr'])) {
                            $val['attr'] = $find['columns'][$val['name']]['attr'];
                        } else {
                            if ($arr = reset(array_slice($find['columns'], $key, 1))) {
                                if (!empty($arr['attr'])) {
                                    $val['attr'] = $arr['attr'];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $params
     */
    protected function getValue(&$params)
    {
        if (isset($params['value']) && !empty($params['value'])) {
            $type = 'text';

            if (isset($params['value']) && $params['value'] === false) {
                $type = 'hidden';
            } else {
                if (is_bool($params['value'])) {
                    $params['value'] = '';
                }
                if (isset($params['value']) && $params['value'] !== '') {
                    $params['value'] = stripcslashes($params['value']);
                } else {
                    $params['value'] = '';
                }
            }

            $params['value'] = '
            <div class="mf-value mf-' . $type . '">
                <input type="' . $type . '" class="form-control form-control-sm" name="' . $params['id'] . '_value" value="' . $params['value'] . '"' . (isset($params['placeholder']) ? ' placeholder="' . $params['placeholder'] . '"' : '') . ' data-value>
            </div>';
        }
    }

    /**
     * @param $params
     */
    protected function columns(&$params)
    {
        if (!empty($params['multi'])) {
            $params['class'] .= ' mf-table-multi';
        }

        if (!isset($params['columns'])) {
            $params['columns'] = [];
        }

        if (!isset($params['types'])) {
            $params['types'] = [];
        }

        if (!empty($params['multi'])) {
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
        }

        $params['types'] = implode('', $params['types']);

        //        $first = reset($params['columns']);
        //        if (!(isset($params['columns']['id']) || empty($first) || (!empty($first) && isset($first['type']) && $first['type'] == 'id'))) {
        //            array_unshift($params['columns'], [
        //                'name' => 'id',
        //                'value' => 'id',
        //                'type' => 'id'
        //            ]);
        //        }

        $columns = '';
        $i = 0;
        foreach ($params['columns'] as $k => &$v) {
            $id = parent::uniqid();

            if (!isset($v['name'])) {
                $v['name'] = $k;
            }

            if (!empty($params['multi'])) {
                $v['name'] = $i;
            }

            if (isset($v['title']) && $v['value'] == '') {
                $v['value'] = $v['title'];
            }

            if (!isset($v['attr'])) {
                $v['attr'] = '';
            }

            if ($v['type'] == 'id') {
                $columns .= parent::element('element')
                    ->render([
                        'id' => '',
                        'tag' => 'div',
                        'class' => 'col',
                        'attr' => 'data-type="id" data-name="id" ' . $v['attr'],
                        'items' => '<input type="text" id="' . $id . '" class="form-control" name="' . $id . '" value="#" readonly>'
                    ]);
            } else {
                $columns .= parent::element('element')
                    ->render([
                        'id' => '',
                        'tag' => 'div',
                        'class' => 'col',
                        'attr' => 'data-type="' . $v['type'] . '" data-name="' . $v['name'] . '"',
                        'items' => '
                        <i class="mf-column-settings fas fa-angle-down" onclick="Multifields.elements.table.columnMenu(event, \'' . $params['id'] . '\')"></i>
                        <input type="text" id="' . $id . '" class="form-control" name="' . $id . '" value="' . $v['value'] . '" onchange="documentDirty=true;">'
                    ]);
            }
            $i++;
        }

        $params['columns.html'] = $columns ? '<div class="mf-columns row m-0 col-12">' . $columns . '</div>' : '';
    }

    /**
     * @param $params
     * @param $data
     */
    protected function items(&$params, &$data)
    {
        if (empty($params['items'])) {
            $params['items'] = [
                'row' => [
                    'type' => 'row',
                    'name' => 'row',
                    'autoincrement' => 'id',
                    'items' => []
                ]
            ];

            $i = 0;
            foreach ($params['columns'] as $k => $v) {
                $v['title'] = '';
                $v['value'] = '';
                if ($v['type'] == 'id') {
                    $v['value'] = $i + 1;
                }
                $params['items']['row']['items'][$k] = $v;
                $i++;
            }
            $params['items'] = parent::renderData($params['items']);
        }

        //        if (empty($params['items'])) {
        //            $params['items'] = [
        //                'row' => [
        //                    'type' => 'row',
        //                    'name' => 'row',
        //                    'autoincrement' => 'id',
        //                    'items' => []
        //                ]
        //            ];
        //
        //            $i = 0;
        //            foreach ($params['columns'] as $k => $v) {
        //                $v['title'] = '';
        //                $v['value'] = '';
        ////                if ($i == 0) {
        ////                    $v['name'] = 'id';
        ////                    $v['type'] = 'id';
        ////                    $v['value'] = $i + 1;
        ////                    $v['item.attr'] = ' readonly';
        ////                }
        //                if ($v['type'] == 'id') {
        //                    $v['value'] = $i + 1;
        //                }
        //                $params['items']['row']['items'][$k] = $v;
        //                $i++;
        //            }
        //            $params['items'] = parent::renderData($params['items']);
        //        } elseif (is_array($params['items'])) {
        //            if (!empty($params['columns'])) {
        //                $i = 1;
        //                foreach ($params['items'] as &$items) {
        //                    if (is_array($items['items'])) {
        //                        $items['type'] = 'row';
        //                        $items['name'] = 'row';
        //                        $items['autoincrement'] = 'id';
        //                        $j = 0;
        //                        foreach ($items['items'] as $k => &$item) {
        //                            if ($j == 0) {
        //                                $item['value'] = $i;
        //                                $item['type'] = 'text';
        //                                $item['item.attr'] = ' readonly';
        //                            } else {
        //                                $item['type'] = isset($params['columns'][$k]) ? $params['columns'][$k]['type'] : 'text';
        //                                $item['attr'] = isset($params['columns'][$k]) ? $params['columns'][$k]['attr'] : '';
        //                            }
        //                            $j++;
        //                        }
        //                        $i++;
        //                    }
        //                }
        //            } else {
        //                $i = 1;
        //                foreach ($params['items'] as &$items) {
        //                    if (!empty($items['autoincrement'])) {
        //                        foreach ($items['items'] as $k => &$item) {
        //                            if ($items['autoincrement'] == $item['name']) {
        //                                $item['value'] = $i;
        //                            }
        //                        }
        //                        $i++;
        //                    }
        //                }
        //            }
        //            $params['items'] = parent::renderData($params['items']);
        //        }
    }

    /**
     * @param array $params
     * @param array $data
     * @return string
     */
    public function render($params = [], $data = [])
    {
        $this->getValue($params);
        $this->columns($params);
        $this->items($params, $data);

        return parent::render($params, $data);
    }

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