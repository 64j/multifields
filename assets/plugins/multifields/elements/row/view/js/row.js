Multifields.element('row', {
  index: 1,

  actionAdd: function(e) {
    if (Multifields.el.classList.contains('mf-row-group')) {
      e.stopPropagation();
      let menu = Multifields.el.querySelector(':scope > .mf-templates');
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
    } else {
      Multifields.getTemplate(function(data) {
        Multifields.el.insertAdjacentHTML('afterend', data.html);
        let clone = Multifields.el.nextElementSibling,
            el,
            items;
        if (Multifields.el.dataset['autoincrement']) {
          items = Multifields.el.parentElement.children;
          let j = 1;
          for (let i = 0; i < items.length; i++) {
            el = items[i].querySelector('[data-type="' + Multifields.el.dataset['autoincrement'] + '"] > input');
            if (el && items[i].dataset['name'] === Multifields.name) {
              el.value = j++;
            }
          }
        }
        Multifields.setDatepicker(clone);
        Multifields.draggable(clone.querySelectorAll(':scope > .mf-items, .mf-draggable > .mf-items'));
      });
    }
  },

  actionDel: function() {
    if (!Multifields.el.parentElement.parentElement.classList.contains('multifields')
        && Multifields.el.parentElement.querySelectorAll('.mf-row[data-name="' + Multifields.name + '"]').length === 1
        && (
            !Multifields.el.parentElement.parentElement.classList.contains('mf-row-group')
            ||
            !Multifields.el.parentElement.parentElement.querySelector('.mf-templates [data-template-name="' + Multifields.name + '"]')
        )
    ) {
      if (Multifields.el.classList.contains('mf-row-group')) {
        Multifields.el.querySelector('.mf-items').innerHTML = '';
      } else {
        Multifields.getTemplate(function(data) {
          Multifields.el.insertAdjacentHTML('afterend', data.html);
          Multifields.elements.row.deleteRow();
        });
      }
    } else {
      Multifields.elements.row.deleteRow();
    }
  },

  deleteRow: function() {
    let el,
        items = Multifields.el.parentElement.children;

    Multifields.el.parentElement.removeChild(Multifields.el);

    let j = 1;
    if (Multifields.el.dataset['autoincrement']) {
      for (let i = 0; i < items.length; i++) {
        el = items[i].querySelector('[data-type="' + Multifields.el.dataset['autoincrement'] + '"] > input');
        if (el && items[i].dataset['name'] === Multifields.name) {
          el.value = j++;
        }
      }
    }
  },

  actionResizeCol: function(e) {
    if (e.button) {
      return true;
    }
    window.getSelection().removeAllRanges();
    let parent = e.target.parentElement.parentElement,
        widthCol = parent.parentElement.offsetWidth / 12,
        col = Math.round(parent.offsetWidth / widthCol),
        className = parent.className = parent.className.replace(/col-[\d|auto]+/g, '').trim() + (col && ' col-' + col || '') + ' mf-active',
        x = e.clientX - parent.offsetWidth,
        helper = parent.querySelector(':scope > .mf-helper') || document.createElement('div'),
        breakpoint = Multifields.toolbar.breakpoint || '';

    if (breakpoint) {
      breakpoint = '-' + breakpoint;
    }

    if (!helper.classList.contains('mf-helper')) {
      helper.className = 'mf-helper';
      parent.appendChild(helper);
    }

    parent.setAttribute('data-mf-disable-col', '');

    document.onmousemove = function(e) {
      window.getSelection().removeAllRanges();
      helper.className = 'mf-helper show';
      helper.innerHTML = 'col' + breakpoint + (col && '-' + col || '');
      if (Math.ceil((e.clientX - x) / widthCol) !== col) {
        col = Math.ceil((e.clientX - x) / widthCol);
        if (col > 12) {
          col = 0;
        } else if (col < 1) {
          col = 'auto';
        }
        parent.className = className.replace(/col-[\d|auto]+/g, '').trim() + (col && ' col-' + col || '');
      }
    };

    document.onmouseup = function(e) {
      parent.className = className.replace(/col-[\d|auto]+/g, '').replace('mf-active', '').trim() + (col ? ' col-' + col : ' col');
      parent.setAttribute('data-mf-col' + breakpoint, col || '');
      for (let k in Multifields.toolbar.breakpoints) {
        if (Multifields.toolbar.breakpoints.hasOwnProperty(k)) {
          if (parent.getAttribute('data-mf-col' + (k && '-' + k || '')) === null) {
            parent.setAttribute('data-mf-col' + (k && '-' + k || ''), '12');
          }
        }
      }
      [...parent.attributes].map(function(attr) {
        if (attr.name.substr(0, 12) === 'data-mf-col-' && typeof Multifields.toolbar.breakpoints[attr.name.substr(12)] === 'undefined') {
          parent.removeAttribute(attr.name);
        }
      });
      parent.removeAttribute('data-mf-disable-col');
      helper.className = 'mf-helper';
      document.onmousemove = null;
      e.preventDefault();
      e.stopPropagation();
    };
  },

  actionResizeOffset: function(e) {
    if (e.button) {
      return true;
    }
    window.getSelection().removeAllRanges();
    let parent = e.target.parentElement.parentElement,
        widthCol = parent.parentElement.offsetWidth / 12,
        offset = Math.round(parent.offsetLeft / widthCol),
        className = parent.className = parent.className.replace(/offset-[\d|auto]+/g, '').trim() + (offset && ' offset-' + offset || '') + ' mf-active',
        x = e.clientX - parent.offsetLeft,
        helper = parent.querySelector(':scope > .mf-helper') || document.createElement('div'),
        breakpoint = Multifields.toolbar.breakpoint || '';

    if (breakpoint) {
      breakpoint = '-' + breakpoint;
    }

    if (!helper.classList.contains('mf-helper')) {
      helper.className = 'mf-helper';
      parent.appendChild(helper);
    }

    parent.setAttribute('data-mf-disable-offset', '');

    document.onmousemove = function(e) {
      window.getSelection().removeAllRanges();
      helper.className = 'mf-helper show';
      helper.innerHTML = 'offset' + breakpoint + (offset && '-' + offset || '');
      if (Math.round((e.clientX - x) / widthCol) !== offset) {
        offset = Math.round((e.clientX - x) / widthCol);
        if (offset > 11) {
          offset = 12;
        } else if (offset < 1) {
          offset = 0;
        }
        parent.className = className.replace(/offset-[\d|auto]+/g, '').trim() + (offset && ' offset-' + offset || '');
      }
    };

    document.onmouseup = function(e) {
      parent.className = className.replace(/offset-[\d|auto]+/g, '').replace('mf-active', '') + (offset && ' offset-' + offset || '');
      if (offset) {
        parent.setAttribute('data-mf-offset' + breakpoint, offset || '');
      } else {
        parent.removeAttribute('data-mf-offset' + breakpoint);
      }
      [...parent.attributes].map(function(attr) {
        if (attr.name.substr(0, 15) === 'data-mf-offset-' && typeof Multifields.toolbar.breakpoints[attr.name.substr(15)] === 'undefined') {
          parent.removeAttribute(attr.name);
        }
      });
      parent.removeAttribute('data-mf-disable-offset');
      helper.className = 'mf-helper';
      document.onmousemove = null;
      e.preventDefault();
      e.stopPropagation();
    };
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

  build: function(el, item, i) {
    if (el.dataset['autoincrement']) {
      item.autoincrement = el.dataset['autoincrement'];
    }
    return item;
  },

  onmousedown: function(e) {
    if (e.target.classList.contains('mf-actions-resize-col')) {
      Multifields.elements.row.actionResizeCol(e);
    }
    if (e.target.classList.contains('mf-actions-resize-offset')) {
      Multifields.elements.row.actionResizeOffset(e);
    }
  }
});