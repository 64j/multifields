Multifields.element('multifields', {

  init: function() {
    Multifields.toolbar = {};

    [...document.querySelectorAll('.multifields')].map(function(el) {
      Multifields.container = el;

      if (!el.querySelector('.mf-breakpoints') && Multifields.cookie.get('mf-breakpoint-' + Multifields.container.dataset.tvId)) {
        Multifields.cookie.del('mf-breakpoint-' + Multifields.container.dataset.tvId);
      }

      el.addEventListener('mousedown', function(e) {
        Multifields.container = el;
        Multifields.el = e.target.closest('[data-type]');
        Multifields.name = Multifields.el.dataset['name'];
        Multifields.type = Multifields.el.dataset['type'];

        Multifields.toolbar = {
          breakpoints: {},
          breakpoint: null
        };

        [...Multifields.container.querySelectorAll('.mf-toolbar > .mf-breakpoints .mf-breakpoint')].map(function(item) {
          if (item.classList.contains('active')) {
            Multifields.toolbar.breakpoint = item.dataset.breakpointName;
          }
          Multifields.toolbar.breakpoints[item.dataset.breakpointName] = {
            key: item.dataset.breakpointKey
          };
        });

        for (let k in Multifields.elements) {
          if (Multifields.elements.hasOwnProperty(k) && k !== 'multifields') {
            Multifields.elements[k]['onmousedown'](e);
          }
        }
      });
    });
  },

  actionAdd: function(e) {
    e.stopPropagation();
    let menu = document.getElementById('mf-templates-' + Multifields.el.id);
    if (menu) {
      Multifields.closeOpened(menu);
      if (menu.children.length > 1) {
        e.target.position = e.target.getBoundingClientRect();
        menu.classList.toggle('open');
        if (e.target.position.top - menu.offsetHeight > 50) {
          menu.removeAttribute('style');
        } else {
          menu.style.bottom = e.target.position.top - menu.offsetHeight - 50 + 'px';
        }
      } else {
        menu.children[0].click();
      }
    }
  },

  setTemplate: function(id) {
    Multifields.getTemplate(id, function(data) {
      let template = document.createElement('template');
      template.innerHTML = data.html;
      Multifields.setDatepicker(template.content);
      Multifields.draggable(template.content.querySelectorAll(':scope > .mf-items, .mf-draggable > .mf-items'));
      Multifields.el.querySelector('.mf-items').appendChild(template.content);
      if (data.type && Multifields.elements[data.type]) {
        Multifields.elements[data.type]['initEl'](Multifields.el.querySelector('.mf-items').lastElementChild);
      }
    }, true);
  },

  actionToolbarBreakpoint: function(key) {
    [...this.parentElement.querySelectorAll('.active')].map(function(item) {
      item.classList.remove('active');
    });
    this.classList.add('active');
    if (parseInt(key)) {
      Multifields.container.querySelector('.mf-items').style.maxWidth = key + 'px';
      Multifields.container.setAttribute('data-mf-breakpoint', this.dataset.breakpointName);
      Multifields.toolbar.breakpoint = this.dataset.breakpointName;
      Multifields.cookie.set('mf-breakpoint-' + Multifields.container.dataset.tvId, this.dataset.breakpointName);
    } else {
      Multifields.container.querySelector('.mf-items').style.maxWidth = '';
      Multifields.container.removeAttribute('data-mf-breakpoint');
      Multifields.toolbar.breakpoint = null;
      Multifields.cookie.del('mf-breakpoint-' + Multifields.container.dataset.tvId);
    }
  },

  actionToolbarExport: function() {
    Multifields.build();
    let blob = new Blob([Multifields.container.nextElementSibling.value || '{}']),
        a = document.createElement('a');
    a.href = URL.createObjectURL.call(this, blob, {
      type: 'text/json;charset=utf-8;'
    });
    a.download = 'multifields-' + Multifields.container.id + '-tv' + Multifields.container.dataset.tvId + '.json';
    document.body.appendChild(a);
    a.click();
    setTimeout(function() {
      window.URL.revokeObjectURL(a.href);
      document.body.removeChild(a);
    }, 0);
  },

  actionToolbarImport: function() {
    let self = this,
        fileInput = document.getElementById('export' + Multifields.container.id) || document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.json';
    fileInput.id = 'export' + Multifields.container.id;
    fileInput.className = 'mf-hidden';
    fileInput.onchange = function() {
      let file = this.files[0],
          reader = new FileReader();
      if (file && ~file.name.indexOf('.json')) {
        reader.onload = function() {
          Multifields.container.nextElementSibling.value = reader.result;
          Multifields.container.querySelector('.mf-items').innerHTML = '';
          Multifields.container.disabled = true;
          self.style.display = 'none';
          if (self.nextElementSibling && self.nextElementSibling.classList.contains('mf-btn-toolbar-save')) {
            self.nextElementSibling.style.display = 'flex';
          }
        };
        reader.readAsText(file);
      }
    };
    Multifields.container.appendChild(fileInput);
    fileInput.click();
  },

  actionToolbarFullscreen: function() {
    if (Multifields.container.hasAttribute('data-mf-fullscreen')) {
      document.body.classList.remove('mf-mode-fullscreen');
      Multifields.container.removeAttribute('data-mf-fullscreen');
      this.classList.remove('active');
      Multifields.cookie.del('mf-fullscreen-' + Multifields.container.dataset.tvId);
    } else {
      document.body.classList.add('mf-mode-fullscreen');
      Multifields.container.setAttribute('data-mf-fullscreen', '');
      this.classList.add('active');
      Multifields.cookie.set('mf-fullscreen-' + Multifields.container.dataset.tvId, 1);
    }
  }

});