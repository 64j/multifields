<?php

namespace Multifields\Elements;

class Row extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/row.css';
    protected $scripts = 'view/js/row.js';
    protected $tpl = 'view/row.tpl';

    protected $actions = [
        'add',
        'move',
        'del',
    ];

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
    protected function getTemplates(&$params)
    {
        $out = '';

        if (!empty($this->getConfig('templates')) && isset($params['templates']) && ($params['templates'] === true || is_array($params['templates']))) {
            $i = 0;
            foreach ($this->getConfig('templates') as $k => $v) {
                if (empty($v['hidden']) && ($params['templates'] === true || (is_array($params['templates']) && in_array($k, $params['templates'])))) {
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