Multifields.element('table', {
  class: 'Multifields\\Elements\\Table',
  el: null,
  menu: null,
  target: null,

  build: function(el, item) {
    item.columns = el.querySelector('.mf-columns');
    if (item.columns && item.columns.children.length) {
      item.columns = Multifields.elements.table.buildColumns(item.columns.children);
    } else {
      delete item.columns;
    }
    return item;
  },

  buildColumns: function(els) {
    let data = [];
    if (els) {
      for (let i = 0; i < els.length; i++) {
        if (els[i].tagName === 'DIV' && els[i].dataset['type']) {
          let item = Object.create(null),
              el;
          item.name = els[i].dataset['name'];
          if (item.name[0] !== '!') {
            item.type = els[i].dataset['type'];
            el = els[i].querySelector('[name]');
            item.value = el && (el.value || el.innerHTML || '');
          }
          data.push(item);
        }
      }
    }
    return data;
  },

  columnMenu: function(e, id) {
    e.stopPropagation();
    Multifields.elements.table.el = Multifields.container.getElementById(id);
    Multifields.elements.table.menu = Multifields.elements.table.el.querySelector('#' + id + ' .mf-column-menu');
    if (Multifields.elements.table.menu) {
      Multifields.elements.table.menu.querySelectorAll('.selected').forEach(function(el) {
        el.classList.remove('selected');
      });
      Multifields.elements.table.menu.style.left = e.target.parentElement.offsetLeft + e.target.parentElement.offsetWidth + 'px';
      if (Multifields.elements.table.target === e.target.parentElement) {
        Multifields.elements.table.menu.classList.toggle('open');
      } else {
        Multifields.elements.table.menu.classList.add('open');
      }
      Multifields.elements.table.target = e.target.parentElement;
      let el = Multifields.elements.table.menu.querySelector('[data-type="' + Multifields.elements.table.target.dataset['type'] + '"]');
      if (el) {
        el.classList.add('selected');
      }
    }
  },

  addColumn: function(e) {
    let el, els, clone;
    let index = [...Multifields.elements.table.target.parentElement.children].findIndex(function(item) {
      return item === Multifields.elements.table.target;
    });
    clone = Multifields.clone(true, Multifields.elements.table.target);
    Multifields.elements.table.target.insertAdjacentElement('afterend', clone);
    clone.querySelector('input').focus();
    els = Multifields.el.querySelector('.mf-items').children;
    for (let i = 0; i < els.length; i++) {
      el = els[i].querySelector('.mf-items').children[index];
      el.insertAdjacentElement('afterend', Multifields.clone(true, el));
    }
    Multifields.elements.table.setIndexes();
  },

  delColumn: function(e) {
    let el, els, cols;
    els = Multifields.elements.table.target.parentElement.children;
    let index = [...Multifields.elements.table.target.parentElement.children].findIndex(function(item) {
      return item === Multifields.elements.table.target;
    });
    if (els.length > 2) {
      els = Multifields.el.querySelector('.mf-items').children;
      for (let i = 0; i < els.length; i++) {
        el = els[i].querySelector('.mf-items').children[index];
        el.parentElement.removeChild(el);
      }
      Multifields.elements.table.target.parentElement.removeChild(Multifields.elements.table.target);
    } else {
      for (let i = 1; i < els.length; i++) {
        els[i].querySelector('input').value = '';
      }
      els = Multifields.el.querySelector('.mf-items').children;
      for (let i = 0; i < els.length; i++) {
        cols = els[i].querySelector('.mf-items').children;
        for (let j = 1; j < cols.length; j++) {
          cols[j].querySelector('input').value = '';
        }
      }
    }
    Multifields.elements.table.setIndexes();
  },

  setType: function(e, type) {
    Multifields.elements.table.target.dataset['type'] = type;
    let index = [...Multifields.elements.table.target.parentElement.children].findIndex(function(item) {
          return item === Multifields.elements.table.target;
        }),
        id = Multifields.uniqid();

    if (type === 'number' && !confirm('If you select the "Number" type, you may lose text data.')) {
      return;
    }

    Multifields.getAction({
      action: 'getElementByType',
      class: this.class,
      type: type,
      name: Multifields.elements.table.target.dataset['name'],
      id: id
    }, function(data) {
      let els = Multifields.elements.table.el.querySelector('.mf-items').children;

      for (let i = 0; i < els.length; i++) {

        let fragment = document.createElement('div'),
            el = els[i].querySelector('.mf-items').children[index],
            input;

        fragment.innerHTML = data.html.replace(new RegExp(id, 'g'), id + '_' + i);
        input = fragment.children[0].querySelector('input');
        input.value = el.querySelector('input').value;

        if (type === 'date') {
          let format = input.dataset['format'];
          new DatePicker(input, {
            yearOffset: dpOffset,
            format: format !== null ? format : dpformat,
            dayNames: dpdayNames,
            monthNames: dpmonthNames,
            startDay: dpstartDay
          });
        }

        el.parentElement.replaceChild(fragment.children[0], el);
      }
    });
  },

  setIndexes: function() {
    let els, row;
    els = Multifields.el.querySelector('.mf-columns');
    if (els) {
      for (let i = 1; i < els.children.length; i++) {
        els.children[i].dataset['name'] = i;
      }
    }
    els = Multifields.el.querySelector('.mf-items');
    if (els) {
      for (let i = 0; i < els.children.length; i++) {
        row = els.children[i].querySelector('.mf-items');
        for (let j = 1; j < row.children.length; j++) {
          row.children[j].dataset['name'] = j;
        }
      }
    }
  }
});
