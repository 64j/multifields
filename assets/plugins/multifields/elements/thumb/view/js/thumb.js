Multifields.element('thumb', {
  popup: null,

  actionDel: function() {
    if (!Multifields.el.offsetParent.classList.contains('mf-row-group') && Multifields.el.parentElement.querySelectorAll('.mf-thumb[data-name="' + Multifields.name + '"]').length === 1) {
      Multifields.el.insertAdjacentElement('afterend', Multifields.clone());
    }
    Multifields.el.parentElement.removeChild(Multifields.el);
  },

  actionEdit: function(e) {
    if (Multifields.el.classList.contains('mf-group')) {
      let clone = Multifields.clone(false);
      if (parent.modx) {
        Multifields.elements.thumb.popup = parent.modx.popup({
          title: Multifields.type,
          content: '<div class="multifields"><div class="mf-items"></div><div><span class="btn btn-success w-100 mf-save">OK</span></div></div>',
          delay: 0,
          overlay: 1,
          draggable: 0,
          hide: 0,
          hover: 0,
          width: '80%',
          onclose: function(e, el) {
            el.classList.remove('show');
            Multifields.elements.thumb.popup = null;
          }
        });
        Multifields.elements.thumb.popup.el.querySelector('.multifields').replaceChild(clone.querySelector('.mf-items'), Multifields.elements.thumb.popup.el.querySelector('.mf-items'));
        Multifields.elements.thumb.popup.el.addEventListener('click', function(e) {
          if (e.target.classList.contains('mf-save')) {
            this.classList.remove('show');
            documentDirty = true;
            this.querySelectorAll('.mf-items [data-thumb]').forEach(function(el) {
              let value = el.querySelector('input').value;
              if (el.dataset['thumb'] === Multifields.el.dataset['name']) {
                Multifields.el.querySelector(':scope > .mf-value input').value = value;
                Multifields.el.style.backgroundImage = 'url(\'/' + value + '\')';
              } else {
                let thumbs = el.dataset['thumb'].split(',');
                for (let k in thumbs) {
                  if (thumbs.hasOwnProperty(k) && Multifields.el.dataset['name'] === thumbs[k]) {
                    Multifields.el.querySelector(':scope > .mf-value input').value = value;
                    Multifields.el.style.backgroundImage = 'url(\'/' + value + '\')';
                    break;
                  }
                }
              }
            });
            Multifields.el.replaceChild(this.querySelector('.mf-items'), Multifields.el.querySelector('.mf-items'));
            this.close();
          }
        });
      } else {
        alert('Not found function parent.modx !');
      }
    } else {
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
              Multifields.el.style.backgroundImage = 'url(\'/' + e.target.value + '\')';
              Multifields.el.parentElement.querySelectorAll('[data-name="' + Multifields.el.dataset['image'] + '"]').forEach(function(el) {
                el.querySelector('[name]').value = e.target.value;
              });
            };
          } else {
            valueEl.onchange = function(e) {
              Multifields.el.style.backgroundImage = 'url(\'/' + e.target.value + '\')';
            };
          }
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
              el = Multifields.clone();
              Multifields.el.insertAdjacentElement('afterend', el);
              Multifields.el = el;
              window.lastFileCtrl = Multifields.el.querySelector('.mf-value > input').id;
              window.SetUrl(files[k]);
              Multifields.el.style.backgroundImage = 'url(\'/' + files[k] + '\')';
            }
          }
        };
      }
    }, 100);
  }
});