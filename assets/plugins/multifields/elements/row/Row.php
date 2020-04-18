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
    ];

    protected $template = '
        <div id="[+id+]" class="mf-row row [+class+]" data-type="row" data-name="[+name+]" [+attr+]>
            [+templates+]
            [+value+]
            [+actions+]
            <div class="mf-items row [+items.class+]">
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
            <div class="mf-value mf-text">
                <input type="text" class="form-control form-control-sm" name="' . $params['id'] . '_value" value="' . stripcslashes($params['value']) . '"' . (isset($params['placeholder']) ? ' placeholder="' . $params['placeholder'] . '"' : '') . ' data-value>
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

                    if ($v['icon'][0] == '<') {
                        $icon = $v['icon'];
                        $icon_class = 'mf-icon mf-icon-image';
                    } elseif (stripos($v['icon'], '/') !== false) {
                        $icon_image = ' style="background-image: url(\'' . $v['icon'] . '\');"';
                        $icon_class = 'mf-icon mf-icon-image';
                    } elseif ($v['icon']) {
                        $icon_class = 'mf-icon ' . $v['icon'];
                    }

                    $out .= '
                    <div class="mf-option" onclick="Multifields.elements.row.template(\'' . $k . '\');">
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

        return parent::render($params, $data);
    }
}
