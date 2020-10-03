var lastImageCtrl;
var lastFileCtrl;

function OpenServerBrowser(url, width, height)
{
  var iLeft = (screen.width - width) / 2;
  var iTop = (screen.height - height) / 2;

  var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes';
  sOptions += ',width=' + width;
  sOptions += ',height=' + height;
  sOptions += ',left=' + iLeft;
  sOptions += ',top=' + iTop;

  var oWindow = window.open(url, 'FCKBrowseWindow', sOptions);
}

function BrowseServer(ctrl)
{
  lastImageCtrl = ctrl;
  var w = screen.width * 0.5;
  var h = screen.height * 0.5;
  OpenServerBrowser(evo.MODX_MANAGER_URL + 'media/browser/' + evo.config.which_browser + '/browser.php?Type=images', w, h);
}

function BrowseFileServer(ctrl)
{
  lastFileCtrl = ctrl;
  var w = screen.width * 0.5;
  var h = screen.height * 0.5;
  OpenServerBrowser(evo.MODX_MANAGER_URL + 'media/browser/' + evo.config.which_browser + '/browser.php?Type=files', w, h);
}

function SetUrlChange(el)
{
  if ('createEvent' in document) {
    var evt = document.createEvent('HTMLEvents');
    evt.initEvent('change', false, true);
    el.dispatchEvent(evt);
  } else {
    el.fireEvent('onchange');
  }
}

function SetUrl(url, width, height, alt)
{
  if (lastFileCtrl) {
    var c = document.getElementById(lastFileCtrl);
    if (c && c.value != url) {
      c.value = url;
      SetUrlChange(c);
    }
    lastFileCtrl = '';
  } else if (lastImageCtrl) {
    var c = document.getElementById(lastImageCtrl);
    if (c && c.value != url) {
      c.value = url;
      SetUrlChange(c);
    }
    lastImageCtrl = '';
  } else {
    return;
  }
}