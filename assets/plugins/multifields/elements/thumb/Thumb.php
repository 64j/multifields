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
        <div class="mf-thumb row [+class+]" data-type="thumb" data-name="[+name+]" [+attr+]>
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
        $params['value'] = '
            <div class="mf-value mf-hidden">
                <input type="hidden" class="form-control form-control-sm" id="' . $params['id'] . '_value" name="' . $params['id'] . '_value" value="' . $params['value'] . '">
            </div>';
    }

    /**
     * @param $params
     * @param $data
     */
    protected function items(&$params, &$data)
    {
        $params['attr'] .= 'style="background-image: url(\'/' . $params['value'] . '\');"';
        if (empty($params['items'])) {
        } elseif (!empty($data) && count($data['items'])) {
            $params['class'] .= 'mf-group';
        }
    }

    /**
     * @param array $params
     * @param array $data
     * @return string
     */
    public function render($params = [], $data = [])
    {
        $this->items($params, $data);
        $this->getValue($params);

        if (!empty($params['multi'])) {
            $params['attr'] .= ' data-multi="' . $params['multi'] . '"';
        }

        if (!empty($params['image'])) {
            $params['attr'] .= ' data-image="' . $params['image'] . '"';
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
