<?php

namespace Multifields\Elements\Row;

use Multifields\Base\Core;

class Row extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/row.css';
    protected $scripts = 'view/js/row.js';

    protected $actions = [
        'add',
        'move',
        'del',
        'resize',
    ];

    protected $template = '
        <div id="[+id+]" class="col mf-row [+class+]" data-type="row" data-name="[+name+]" [+attr+]>
            [+label+]
            [+templates+]
            [+value+]
            [+actions+]
            <div class="mf-items [+items.class+]">
                [+items+]
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
                <div class="mf-value">
                    <input type="text" class="form-control" name="' . $params['id'] . '_value" value="' . stripcslashes($params['value']) . '"' . (isset($params['placeholder']) ? ' placeholder="' . $params['placeholder'] . '"' : '') . ' data-value>
                </div>';
        }
    }

    /**
     * @param $params
     */
    protected function getTemplates(&$params)
    {
        $out = '';

        if (!empty(Core::getConfig('templates')) && isset($params['templates']) && ($params['templates'] === true || is_array($params['templates']))) {
            $i = 0;
            foreach (Core::getConfig('templates') as $k => $v) {
                if ((empty($v['hidden']) && empty($params['templates'])) || ($params['templates'] === true || (is_array($params['templates']) && (isset($params['templates'][$k]) || in_array($k, $params['templates']))))) {
                    $v['title'] = isset($v['title']) ? $v['title'] : $k;
                    $v['icon'] = isset($v['icon']) ? $v['icon'] : '';
                    $icon = '';
                    $icon_class = '';
                    $icon_image = '';

                    if (!empty($v['icon']) && $v['icon'][0] == '<') {
                        $icon = $v['icon'];
                        $icon_class = 'mf-icon mf-icon-image';
                    } elseif (stripos($v['icon'], '/') !== false) {
                        $icon_image = ' style="background-image: url(\'' . $v['icon'] . '\');"';
                        $icon_class = 'mf-icon mf-icon-image';
                    } elseif ($v['icon']) {
                        $icon_class = 'mf-icon ' . $v['icon'];
                    }

                    $out .= '
                    <div class="mf-option" onclick="Multifields.elements.row.template(\'' . $k . '\');" data-template-name="' . $k . '">
                        <div class="' . $icon_class . '"' . $icon_image . '>' . $icon . '</div>' . $v['title'] . '
                    </div>';
                    $i++;
                }
            }

            if (!empty($out)) {
                $params['class'] .= ' mf-row-group';

                $out = '<div id="mf-templates-' . $params['id'] . '" class="mf-templates' . ($i > 1 ? '' : ' mf-hidden') . ' contextMenu">' . $out . '</div>';
            }
        }

        $params['templates'] = $out;
    }

    /**
     * @param array $params
     * @param array $data
     * @return string
     */
    public function render($params = [], $data = [])
    {
        $this->getValue($params);
        $this->getTemplates($params);

        if (!empty($params['autoincrement'])) {
            $params['attr'] .= ' data-autoincrement="' . $params['autoincrement'] . '"';
        }

        if (!empty($params['label'])) {
            $params['label'] = '<div class="mf-title" ' . $params['label'] . '>' . $params['label'] . '</div>';
        }

        if (!empty($params['mf.col'])) {
            $params['class'] = trim(preg_replace('/col-[\d]+/', '', $params['class'])) . ' col-' . $params['mf.col'];
            $params['attr'] .= ' data-mf.col="' . $params['mf.col'] . '"';
        }

        if (!empty($params['mf.offset'])) {
            $params['class'] = trim(preg_replace('/offset-[\d]+/', '', $params['class'])) . ' offset-' . $params['mf.offset'];
            $params['attr'] .= ' data-mf.offset="' . $params['mf.offset'] . '"';
        }

        return parent::render($params, $data);
    }

    protected function renderAction($action, $type)
    {
        if ($action == 'resize') {
            return '
                <i class="mf-actions-' . $action . '-offset fa" onmousedown="Multifields.elements[\'' . $type . '\'].action' . ucfirst($action) . 'Offset(event);"></i>
                <i class="mf-actions-' . $action . '-col fa" onmousedown="Multifields.elements[\'' . $type . '\'].action' . ucfirst($action) . 'Col(event);"></i>';
        }

        return parent::renderAction($action, $type);
    }
}
