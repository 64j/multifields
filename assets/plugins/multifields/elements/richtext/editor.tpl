<!doctype html>
<html lang="[+lang+]">
<head>
    <base href="[+MODX_SITE_URL+]">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MultiFields:: RichText</title>
    <link rel="stylesheet" href="[+MGR_DIR+]/media/style/[+manager_theme+]/style.css">
    <style>
        html, body, body > form { position: relative; margin: 0; padding: 0; height: 100%; background: none }
        #table-layout { position: relative; width: 100%; height: 100%; padding: 0; border: none; border-collapse: collapse; }
        #table-layout > tfoot > tr { height: 2rem; }
        #table-layout > tbody > tr > td { padding: 0 }
        #table-layout > tbody > tr > td > .mce-tinymce { height: 100% !important; border: none !important; }
        #table-layout > tbody > tr > td > .mce-tinymce > .mce-container-body { display: table !important; width: 100%; height: 100%; }
        #table-layout > tbody > tr > td > .mce-tinymce > .mce-container-body > div { display: table-row !important; height: 1%; }
        #table-layout > tbody > tr > td > .mce-tinymce > .mce-container-body > div.mce-edit-area { height: auto }
        #table-layout iframe { height: 100% !important; }
        textarea, textarea + div { height: 100% !important; border: none !important; }
        #actions { display: flex }
        #actions .fa { pointer-events: none; }
    </style>
    <script>
      if (!parent.modx) {
        parent.modx = {
          tree: {}
        };
      }
    </script>
</head>
<body class="[+body_class+]">
<form id="ta_form" name="ta_form" method="post">
    <input type="hidden" name="editor" id="editor" value="">
    <div id="actions">
        <button type="submit" class="btn btn-sm btn-success btn-save">
            <i class="fa fa-floppy-o show"></i>
        </button>
        <span class="btn btn-sm btn-danger btn-close">
            <i class="fa fa-times-circle show"></i>
        </span>
    </div>
    <table id="table-layout">
        <tbody>
        <tr>
            <td>
                <textarea name="ta" id="ta" cols="30" rows="10"></textarea>
            </td>
        </tr>
        </tbody>
    </table>
</form>
[+evtOut+]
</body>
</html>