Multifields.element('thumb', {
  popup: null,
  parent: null,

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
    if (Multifields.el.classList.contains('mf-group')) {
      let clone = Multifields.clone(false);
      if (parent.modx) {
        Multifields.elements.thumb.popup = parent.modx.popup({
          title: Multifields.el.querySelector('.mf-title') && Multifields.el.querySelector('.mf-title').innerHTML || Multifields.type,
          content: '<div class="multifields"><div class="mf-items"></div><div class="px-1"><span class="btn btn-success w-100 mf-save">OK</span></div></div>',
          icon: 'fa-layer-group',
          delay: 0,
          overlay: 1,
          overlayclose: 0,
          hide: 0,
          hover: 0,
          width: '80%',
          onclose: function(e, el) {
            el.classList.remove('show');
            Multifields.elements.thumb.popup = null;
          }
        });
        Multifields.elements.thumb.popup.el.querySelector('.multifields').replaceChild(clone.querySelector('.mf-items'), Multifields.elements.thumb.popup.el.querySelector('.mf-items'));
        Multifields.elements.thumb.parent = Multifields.el;

        // init Richtext
        Multifields.elements.richtext.initEls(Multifields.elements.thumb.popup.el, true);

        Multifields.elements.thumb.popup.el.addEventListener('mousedown', function(e) {
          let target = e.target.hasAttribute('data-type') && e.target || e.target.closest('[data-type]');
          if (target) {
            Multifields.el = target;
            Multifields.name = Multifields.el.dataset['name'];
            Multifields.type = Multifields.el.dataset['type'];
          }
        });

        Multifields.elements.thumb.popup.el.addEventListener('click', function(e) {
          if (e.target.classList.contains('mf-save')) {
            this.classList.remove('show');
            documentDirty = true;
            [...this.querySelectorAll('.mf-items [data-thumb]')].map(function(el) {
              let value = el.querySelector('input').value;
              if (el.dataset['thumb'] === Multifields.elements.thumb.parent.dataset['name']) {
                Multifields.elements.thumb.parent.querySelector(':scope > .mf-value input').value = value;
                Multifields.elements.thumb.parent.style.backgroundImage = 'url(\'../' + value + '\')';
              } else {
                let thumbs = el.dataset['thumb'].toString().split(',');
                for (let k in thumbs) {
                  if (thumbs.hasOwnProperty(k) && Multifields.elements.thumb.parent.dataset['name'] === thumbs[k]) {
                    Multifields.elements.thumb.parent.querySelector(':scope > .mf-value input').value = value;
                    Multifields.elements.thumb.parent.style.backgroundImage = 'url(\'../' + value + '\')';
                    break;
                  }
                }
              }
            });
            Multifields.elements.thumb.parent.replaceChild(this.querySelector('.mf-items'), Multifields.elements.thumb.parent.querySelector('.mf-items'));
            // save Richtext
            Multifields.elements.richtext.destroyEls(Multifields.elements.thumb.parent);
            this.close();
          }
        });
        Multifields.elements.thumb.popup.el.addEventListener('change', function(e) {
          let target = e.target;
          switch (target.type) {
            case 'select':
            case 'select-one':
            case 'select-multiple':
              [...target.options].map(function(el, i) {
                if (i === target.selectedIndex) {
                  el.setAttribute('selected', true);
                } else {
                  el.removeAttribute('selected');
                }
              });
              break;
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