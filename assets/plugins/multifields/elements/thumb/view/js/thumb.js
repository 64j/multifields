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
    let Thumb = this;

    if (Multifields.el.classList.contains('mf-group')) {
      if (parent.modx) {
        Thumb.popup = parent.modx.popup({
          title: Multifields.el.querySelector('.mf-title') && Multifields.el.querySelector('.mf-title').innerHTML || Multifields.type,
          content: '<div class="multifields"><div class="mf-items"></div></div>',
          icon: 'fa-layer-group',
          delay: 0,
          overlay: 1,
          overlayclose: 0,
          hide: 0,
          hover: 0,
          width: '80%',
          maxheight: '99%',
          position: 'top center',
          onclose: function(e, el) {
            el.classList.remove('show');
            Thumb.popup = null;
          }
        });

        Thumb.clone = Multifields.el.querySelector(':scope > .mf-items').cloneNode(true).children;

        Thumb.popup.el.querySelector('.multifields .mf-items').append(...Multifields.el.querySelector(':scope > .mf-items').children);

        Thumb.popup.el.querySelector('.evo-popup-close').outerHTML = '<div id="actions" class="position-absolute"><span class="btn btn-sm btn-success mf-save"><i class="fa fa-floppy-o show no-events"></i></span><span class="btn btn-sm btn-danger mf-close"><i class="fa fa-times-circle show no-events"></i></span></div>';

        Thumb.parent = Multifields.el;

        Multifields.setDatepicker(Thumb.popup.el);

        Multifields.draggable(Thumb.popup.el.querySelectorAll('.multifields > .mf-items, .mf-draggable > .mf-items'));

        // Init elements
        [...Thumb.popup.el.querySelectorAll('[data-type]')].map(function(el) {
          let type = el.dataset.type;
          if (Multifields.elements[type]) {
            Multifields.elements[type]['initEl'](el);
          }
        });

        Thumb.popup.el.addEventListener('mousedown', function(e) {
          let target = e.target.hasAttribute('data-type') && e.target || e.target.closest('[data-type]');
          if (target) {
            Multifields.el = target;
            Multifields.name = Multifields.el.dataset['name'];
            Multifields.type = Multifields.el.dataset['type'];
          }
        });

        Thumb.popup.el.addEventListener('click', function(e) {
          if (e.target.classList.contains('mf-save')) {
            this.classList.remove('show');
            documentDirty = true;
            [...this.querySelectorAll('.mf-items [data-thumb]')].map(function(el) {
              let value = el.querySelector('input').value;
              if (el.dataset['thumb'] === Thumb.parent.dataset['name']) {
                Thumb.parent.querySelector(':scope > .mf-value input').value = value;
                Thumb.parent.style.backgroundImage = 'url(\'../' + value + '\')';
              } else {
                let thumbs = el.dataset['thumb'].toString().split(',');
                for (let k in thumbs) {
                  if (thumbs.hasOwnProperty(k) && Thumb.parent.dataset['name'] === thumbs[k]) {
                    Thumb.parent.querySelector(':scope > .mf-value input').value = value;
                    Thumb.parent.style.backgroundImage = 'url(\'../' + value + '\')';
                    break;
                  }
                }
              }
            });
            // save Richtext
            Thumb.parent.querySelector('.mf-items').append(...this.querySelector('.mf-items').children);
            Multifields.elements.richtext.destroyEls(Thumb.parent);
            this.close();
          }
          if (e.target.classList.contains('mf-close')) {
            Thumb.parent.querySelector('.mf-items').append(...Thumb.clone);
            Multifields.elements.richtext.destroyEls(Thumb.parent, false);
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
            Thumb.MultiBrowseServer(valueEl);
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
              let template = Multifields.el.dataset['multi'] || Multifields.name,
              el = Multifields.el.parentElement.closest('[data-type]');
              el = el.dataset['name'] === template ? el : el.querySelector('[data-name="' + template + '"]')
              Multifields.getTemplate(template, function(data) {
                el.insertAdjacentHTML('afterend', data.html);
                Multifields.el = el.nextElementSibling.dataset['name'] === Multifields.name && el.nextElementSibling || el.nextElementSibling.querySelector('[data-name="' + Multifields.name + '"]');
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
