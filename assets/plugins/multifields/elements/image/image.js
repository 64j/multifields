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

Multifields.element('image', {
  setValue: function(e) {
    let target = e.target,
        items = target.closest('.mf-items'),
        thumbs = target.parentElement.dataset['thumb'].split(','),
        thumb;
    for (let k in thumbs) {
      if (thumbs.hasOwnProperty(k)) {
        thumb = items.querySelector('[data-name="' + thumbs[k] + '"]');
        if (thumb) {
          thumb.querySelector(':scope > .mf-value input').value = target.value;
          thumb.style.backgroundImage = 'url(\'../' + target.value + '\')';
        }
      }
    }
  },

  MultiBrowseServer: function(e) {
    Multifields.interval = setInterval(function() {
      if (window.KCFinder) {
        clearInterval(Multifields.interval);
        window.KCFinder.callBackMultiple = function(files) {
          let el = document.getElementById(e.target.previousElementSibling.id),
              clone;
          window.KCFinder = null;
          window.lastFileCtrl = e.target.id;
          window.SetUrl(files[0]);

          if (Multifields.elements.thumb.popup) {
            Multifields.elements.thumb.popup.el.querySelector('.mf-save').click();
            for (let k in files) {
              if (files.hasOwnProperty(k) && k !== '0') {
                clone = Multifields.clone(true);
                Multifields.el.insertAdjacentElement('afterend', clone);
                Multifields.el = clone;
                el = clone.querySelector('[data-name="' + el.parentElement.dataset['name'] + '"] > input');
                if (el) {
                  window.lastFileCtrl = el.id;
                  window.SetUrl(files[k]);
                  Multifields.el.querySelector(':scope > .mf-value input').value = files[k];
                  Multifields.el.style.backgroundImage = 'url(\'../' + files[k] + '\')';
                }
              }
            }
          } else {
            let parent = el.parentElement.closest('[data-name="' + el.parentElement.dataset['multi'] + '"]');
            for (let k in files) {
              if (files.hasOwnProperty(k) && k !== '0') {
                clone = Multifields.clone(true, parent);
                parent.insertAdjacentElement('afterend', clone);
                parent = clone;
                el = parent.querySelector('[data-name="' + el.parentElement.dataset['name'] + '"] > input');
                if (el) {
                  window.lastFileCtrl = el.id;
                  window.SetUrl(files[k]);
                }
              }
            }
          }
        };
      }
    }, 100);
  }
});
