Multifields.element('thumb:image', {

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
        BrowseServer(valueEl.id);
        if (Multifields.el.dataset['multi']) {
          Multifields.elements.thumb.MultiBrowseServer(valueEl);
        }
        if (Multifields.el.dataset['image']) {
          valueEl.onchange = function(e) {
            Multifields.el.style.backgroundImage = 'url(\'../' + e.target.value + '\')';
            [...Multifields.el.parentElement.querySelectorAll('[data-name="' + Multifields.el.dataset['image'] + '"]')].map(function(el) {
              el.querySelector('[name]').value = e.target.value;
            });
          };
        } else {
          valueEl.onchange = function(e) {
            Multifields.el.style.backgroundImage = 'url(\'../' + e.target.value + '\')';
          };
        }
      }
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
                Multifields.el.style.backgroundImage = 'url(\'../' + files[k] + '\')';
              });
            }
          }
        };
      }
    }, 100);
  }
});