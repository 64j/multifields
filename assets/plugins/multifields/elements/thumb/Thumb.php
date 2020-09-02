<?php

namespace Multifields\Elements\Thumb;

class Thumb extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/thumb.css';
    protected $scripts = 'view/js/thumb.js';

    protected $actions = [
        'add',
        'move',
        'del',
        'edit'
    ];

    protected $template = '
        <div class="col mf-thumb [+class+]" data-type="thumb" data-name="[+name+]" [+attr+]>
            [+title+]
            [+value+]
            [+actions+]
            <div class="row mx-0 mb-2 col-12 p-0 mf-items [+items.class+]">
                [+items+]
            </div>
        </div>';

    /**
     * @param $params
     */
    protected function setBackground(&$params)
    {
        preg_match('/style="(.*)"/', $params['attr'], $matches);
        $params['attr'] = preg_replace('/style="(.*)"/', '', $params['attr']);
        $params['attr'] .= 'style="background-image: url(\'/' . $params['value'] . '\');' . (!empty($matches[1]) ? $matches[1] : '') . '"';
    }

    /**
     * @param $params
     */
    protected function getValue(&$params)
    {
        $params['value'] = '
            <div class="mf-value mf-hidden">
                <input type="hidden" id="' . $params['id'] . '_value" name="' . $params['id'] . '_value" value="' . $params['value'] . '">
            </div>';
    }

    /**
     * @param array $params
     * @param array $data
     * @return string
     */
    public function render($params = [], $data = [])
    {
        $this->setBackground($params);
        $this->getValue($params);

        if (!empty($params['title'])) {
            $params['title'] = '<div class="mf-title">' . $params['title'] . '</div>';
        } else {
            $params['title'] = '';
        }

        if (!empty($params['multi'])) {
            $params['attr'] .= ' data-multi="' . $params['multi'] . '"';
        }

        if (!empty($params['image'])) {
            $params['attr'] .= ' data-image="' . $params['image'] . '"';
        }

        if (!empty($data) && !empty($data['items'])) {
            $params['class'] .= ' mf-group';
        }

        return parent::render($params, $data);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function findImage($data = [])
    {
        $out = [];
        foreach ($data as $k => $v) {
            if (empty($v['items'])) {
                if ($v['type'] == 'image' && !empty($v['thumb'])) {
                    foreach ($v['thumb'] as $thumb) {
                        $out[$thumb] = $v['value'];
                    }
                }
            } else {
                $out = $this->findImage($v['items']);
            }
        }

        return $out;
    }
}
