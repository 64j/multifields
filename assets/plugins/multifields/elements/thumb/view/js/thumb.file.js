Multifields.element('thumb:file', {

  actionDel: function() {
    if (!Multifields.el.parentElement.parentElement.classList.contains('multifields')
        && (
            !Multifields.el.parentElement.parentElement.classList.contains('mf-row-group')
            && Multifields.el.parentElement.querySelectorAll('.mf-thumb[data-name="' + Multifields.name + '"]').length === 1
        )
    ) {
      Multifields.getTemplate(function(data) {
        Multifields.el.insertAdjacentHTML('afterend', data.html);
        Multifields.el.parentElement.removeChild(Multifields.el);
      });
    } else {
      Multifields.el.parentElement.removeChild(Multifields.el);
    }
  },

  actionEdit: function() {
    let els = Multifields.el.querySelector('.mf-items');
    if (els && els.children.length) {
      let btn = Multifields.el.querySelector('[type="button"]');
      if (btn) {
        btn.click();
      }
    } else {
      let valueEl = Multifields.el.querySelector(':scope > .mf-value input');
      if (valueEl) {
        BrowseFileServer(valueEl.id);
        if (Multifields.el.dataset['multi']) {
          Multifields.elements['thumb:file'].MultiBrowseServer(valueEl);
        }
      }
    }
  },

  actionHide: function() {
    if (!Multifields.el.dataset.mfHide) {
      Multifields.el.setAttribute('data-mf-hide', 1);
    } else {
      Multifields.el.removeAttribute('data-mf-hide');
    }
  },

  MultiBrowseServer: function(el) {
    Multifields.interval = setInterval(function() {
      if (window.KCFinder) {
        clearInterval(Multifields.interval);
        window.KCFinder.callBackMultiple = function(files) {
          window.KCFinder = null;
          window.lastFileCtrl = el.id;
          window.SetUrl(files[0]);
          for (let k in files) {
            if (files.hasOwnProperty(k) && k !== '0') {
              Multifields.getTemplate(function(data) {
                Multifields.el.insertAdjacentHTML('afterend', data.html);
                Multifields.el = Multifields.el.nextElementSibling;
                window.lastFileCtrl = Multifields.el.querySelector('.mf-value > input').id;
                window.SetUrl(files[k]);
              });
            }
          }
        };
      }
    }, 100);
  }

});