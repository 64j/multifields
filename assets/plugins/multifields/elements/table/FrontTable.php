<?php

namespace Multifields\Elements\Table;

class FrontTable extends \Multifields\Base\FrontElements
{
    /**
     * @param array $data
     * @param array $params
     * @return array
     */
    protected function afterFindData($data = [], &$params = [])
    {
        if (!empty($params['columns']) && !empty($data['items'])) {
            $columns = array_replace_recursive(array_values($params['columns']), $data['columns']);

            $items = [];
            $row = !empty($params['items']['row']) ? $params['items']['row'] : [];
            if (!empty($row)) {
                $row['items'] = $columns;
                $items[] = $row;
            }
            $data['items'] = array_merge($items, $data['items']);

            foreach ($data['items'] as &$items) {
                if (!empty($params['items'])) {
                    $items += $params['items'];
                }
                if (!empty($items['items'])) {
                    foreach ($items['items'] as $k => &$item) {
                        if (isset($columns[$k]['tpl'])) {
                            $item['tpl'] = $columns[$k]['tpl'];
                        }
                    }
                }
            }
            unset($data['columns']);
        }

        return $data;
    }
}
