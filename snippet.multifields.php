<?php
/**
 * Snippet multifields
 * @author 64j
 */

if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

if (!empty($tv)) {
    $config = array();
    $config['tv'] = $tv;
    $config['id'] = !empty($id) ? $id : $modx->documentIdentifier;
    $config['tpl'] = !empty($tpl) ? $tpl : '@CODE:[+value+]';
    $config['tplWrap'] = !empty($tplWrap) ? $tplWrap : '@CODE:[+wrap+]';
    $config['tplGroup'] = !empty($tplGroup) ? $tplGroup : '@CODE:<div class="group">[+group+]</div>';
    $config['tplSection'] = !empty($tplSection) ? $tplSection : '@CODE:<div class="section">[+section+]</div>';
    $config['tplRows'] = !empty($tplRows) ? $tplRows : '@CODE:<div class="rows">[+rows+]</div>';
    $config['tplRow'] = !empty($tplRow) ? $tplRow : '@CODE:<div class="row">[+row+]</div>';
    $config['tplItems'] = !empty($tplItems) ? $tplItems : '@CODE:[+items+]';
    $config['tplThumb'] = !empty($tplThumb) ? $tplThumb : '@CODE:[+thumb+]';
    $config['noneTPL'] = !empty($noneTPL) ? $noneTPL : '';
    $config['templateExtension'] = !empty($templateExtension) ? $templateExtension : '';
    $config['templatePath'] = !empty($templatePath) ? $templatePath : '';
    $config['schema'] = '';
    $config['render'] = true;
    $config['value'] = array();

    foreach ($modx->event->params as $key => $value) {
        if (strpos($key, 'tpl') !== false) {
            $config[$key] = $value;
        }
    }

    if (!empty($modx->documentObject[$config['tv']])) {
        $sql = $modx->db->query('
        SELECT tv.*
        FROM ' . $modx->getFullTableName('site_tmplvars') . ' AS tv
        WHERE tv.name="' . $config['tv'] . '" AND tv.locked=0
        LIMIT 1');

        while ($row = $modx->db->getRow($sql)) {
            if (!empty($row['elements'])) {
                $templates = json_decode($row['elements'], true);
                $config['schema'] = !empty($templates['schema']) ? $templates['schema'] : '';
                $config['templates'] = !empty($templates['templates']) ? $templates['templates'] : array();
            }
            $config['field_id'] = $row['id'];
        }

        $config['value'] = json_decode($modx->documentObject[$config['tv']][1], true);
    } else {
        $sql = $modx->db->query('
        SELECT tv.*, tvc.contentid, tvc.value
        FROM ' . $modx->getFullTableName('site_tmplvars') . ' AS tv
        LEFT JOIN ' . $modx->getFullTableName('site_tmplvar_contentvalues') . ' AS tvc ON tvc.tmplvarid=tv.id
        WHERE tv.name="' . $config['tv'] . '" AND tvc.contentid=' . $config['id'] . ' AND tv.locked=0
        LIMIT 1');

        while ($row = $modx->db->getRow($sql)) {
            if (!empty($row['elements'])) {
                $templates = json_decode($row['elements'], true);
                $config['schema'] = !empty($templates['schema']) ? $templates['schema'] : '';
                $config['templates'] = !empty($templates['templates']) ? $templates['templates'] : array();
            }
            if (!empty($row['value'])) {
                $config['value'] = json_decode($row['value'], true);
            }
            $config['field_id'] = $row['id'];
        }
    }

    require_once 'class.multifields.php';
    $controller = new customTvMultifields($config, $modx);

    echo $controller->render();
}
