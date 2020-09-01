<?php

namespace Multifields\Elements\Thumb;

class File extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/thumb.file.css';
    protected $scripts = 'view/js/thumb.file.js';

    protected $actions = [
        'add',
        'move',
        'del',
        'edit'
    ];

    protected $template = '
        <div class="col mf-thumb mf-thumb-file [+class+]" data-type="thumb:file" data-name="[+name+]" [+attr+]>
            [+title+]
            [+value+]
            [+actions+]
        </div>';

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
        $this->getValue($params);

        if (!empty($params['title'])) {
            $params['title'] = '<div class="px-2 py-1 mf-title">' . $params['title'] . '</div>';
        } else {
            $params['title'] = '';
        }

        if (!empty($params['multi'])) {
            $params['attr'] .= ' data-multi="' . $params['multi'] . '"';
        }

        if (isset($params['items'])) {
            unset($params['items']);
        }

        return parent::render($params, $data);
    }
}
