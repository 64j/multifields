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
//                    if (empty($v['icon.class']) && empty($v['icon'])) {
//                        $v['icon'] = ' style="background-image: url(\'/' . str_replace(MODX_BASE_PATH, '', str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__))) . '/' . $v['type'] . '/' . $v['type'] . '.svg\')"';
//                    }
                    $out .= '
                    <div class="mf-option" onclick="Multifields.elements.multifields.template(\'' . $k . '\');">
                        <div class="mf-icon ' . $v['icon.class'] . '"' . $v['icon'] . '></div>' . $v['title'] . '
                    </div>';
                    $i++;
                }
            }

            if (!empty($out)) {
                $params['class'] .= ' mf-row-group';
                if (!empty($this->getConfig('settings')['view']) && in_array($this->getConfig('settings')['view'], $this->settings['view'])) {
                    $params['class'] .= ' mf-view-' . $this->getConfig('settings')['view'];
                }
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
        $this->getTemplates($params);

        return parent::render($params, $data);
    }
}
