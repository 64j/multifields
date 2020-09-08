Multifields.element('multifields', {

  init: function() {
    Multifields.toolbar = {};

    [...document.querySelectorAll('.multifields')].map(function(el) {
      Multifields.container = el;

      if (!el.querySelector('.mf-breakpoints') && Multifields.cookie.get('data-mf-breakpoint-' + Multifields.container.dataset.tvId)) {
        Multifields.cookie.del('data-mf-breakpoint-' + Multifields.container.dataset.tvId);
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

  template: function(id) {
    Multifields.getTemplate(id, function(data) {
      let template = document.createElement('template');
      template.innerHTML = data.html;
      Multifields.setDatepicker(template.content);
      Multifields.draggable(template.content.querySelectorAll(':scope > .mf-items, .mf-draggable > .mf-items'));

      if (Multifields.container.querySelector('.mf-selected')) {
        Multifields.container.querySelector('.mf-selected').appendChild(template.content);
      } else {
        Multifields.el.querySelector('.mf-items').appendChild(template.content);
      }
    });
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
      Multifields.cookie.set('data-mf-breakpoint-' + Multifields.container.dataset.tvId, this.dataset.breakpointName);
    } else {
      Multifields.container.querySelector('.mf-items').style.maxWidth = '';
      Multifields.container.removeAttribute('data-mf-breakpoint');
      Multifields.toolbar.breakpoint = null;
      Multifields.cookie.del('data-mf-breakpoint-' + Multifields.container.dataset.tvId);
    }
  },

  actionToolbarFullscreen: function() {
    if (Multifields.container.hasAttribute('data-mf-fullscreen')) {
      document.body.style.overflow = '';
      Multifields.container.removeAttribute('data-mf-fullscreen');
      this.classList.remove('active');
    } else {
      document.body.style.overflow = 'hidden';
      Multifields.container.setAttribute('data-mf-fullscreen', '');
      this.classList.add('active');
    }
  }

});