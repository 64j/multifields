<?php

namespace Multifields\Elements;

class Thumb extends \Multifields\Base\Elements
{
    protected $styles = 'view/css/thumb.css';
    protected $scripts = 'view/js/thumb.js';
    protected $tpl = 'view/thumb.tpl';

    protected $actions = [
        'add',
        'move',
        'del',
        'edit'
    ];

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

    /**
     * @param array $params
     * @param array $data
     * @return string
     */
    public function render2($params = [], $data = [])
    {
        // нет вложенных элементов
        if (empty($params['items'])) {
            if (!empty($params['image'])) {
                $params['attr'] .= ' data-image="' . $params['image'] . '"';
            }

            if (empty($params['value']) && !empty($data['items'][0]['value'])) {
                $params['value'] = $data['items'][0]['value'];
            }

            $params['items'] = $this->renderFormElement([
                'type' => 'image',
                'name' => 'image',
                'class' => $params['class'],
                'thumb' => $params['name'],
                'multi' => empty($params['multi']) ? '' : $params['multi'],
                'value' => $params['value'],
                'attr' => $params['attr']
            ]);
        } // есть элементы
        elseif (!empty($data) && count($data['items'])) {
            // больше одного
            if (count($data['items']) > 1) {
                $params['class'] .= 'mf-group';
                if ($thumbs = $this->findImage($data['items'])) {
                    if (isset($thumbs[$params['name']])) {
                        $params['attr'] .= 'style="background-image: url(\'/' . $thumbs[$params['name']] . '\');"';
                        unset($thumbs[$params['name']]);
                    }
                    if (!empty($thumbs)) {
                        foreach ($thumbs as $k => $thumb) {
                            $params['items'] = str_replace('data-name="' . $k . '"', 'data-name="' . $k . '" style="background-image: url(\'/' . $thumb . '\');"', $params['items']);
                        }
                    }
                }
            } else {
                // ставим картинку если тип image
                if ($data['items'][0]['type'] == 'image' && !empty($data['items'][0]['value'])) {
                    $params['attr'] .= ' style="background-image: url(\'/' . $data['items'][0]['value'] . '\');"';
                }

                //                preg_match('/id="(.*?)"/', $params['items'], $matches);
                //                $id = $matches[1];

                $attr = 'data-thumb="' . $params['name'] . '"';
                $onchange = 'Multifields.elements.image.events.change(event);';
                $onclick = '';

                if (!empty($params['multi'])) {
                    $attr .= ' data-multi="' . $params['multi'] . '"';
                    $onclick = 'Multifields.elements.image.MultiBrowseServer(event);';
                }

                if (!empty($params['image'])) {
                    $attr .= ' data-image="' . $params['image'] . '"';
                }

                $params['items'] = preg_replace([
                    '/data-name="/',
                    '/onchange="(.*?)"/',
                    '/onclick="(.*?)"/'
                ], [
                    $attr . ' data-name="',
                    'onchange="$1' . $onchange . '"',
                    'onclick="$1' . $onclick . '"'
                ], $params['items']);
            }
        }

        return parent::render($params, $data);
    }
}