<?php

namespace Multifields\Elements;

class Multifields extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/multifields.css';
    protected $scripts = 'view/js/multifields.js';
    protected $tpl = 'view/multifields.tpl';

    protected $actions = [
        'add'
    ];

    protected $settings = [
        'view' => [
            'icons'
        ]
    ];

    /**
     * @param $params
     */
    protected function getTemplates(&$params)
    {
        $out = '';

        if (!empty($this->getConfig('templates'))) {
            $i = 0;
            foreach ($this->getConfig('templates') as $k => $v) {
                if (empty($v['hidden'])) {
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
                    <div class="mf-option" onclick="Multifields.elements.multifields.template(\'' . $k . '\');">
                        <div class="' . $icon_class . '"' . $icon_image . '>' . $icon . '</div>' . $v['title'] . '
                    </div>';
                    $i++;
                }
            }

            if (!empty($out)) {
                $class = '';
                $params['class'] .= ' mf-row-group';
                if (!empty($this->getConfig('settings')['view']) && in_array($this->getConfig('settings')['view'], $this->settings['view'])) {
                    $params['class'] .= ' mf-view-' . $this->getConfig('settings')['view'];
                } else {
                    $class = ' contextMenu';
                }
                $out = '<div id="mf-templates-' . $params['id'] . '" class="mf-templates' . ($i > 1 ? '' : ' mf-hidden') . $class . '">' . $out . '</div>';
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
        $this->getTemplates($params);

        return parent::render($params, $data);
    }
}
