Multifields.element('row', {
  class: 'Multifields\\Elements\\Row\\Row',
  index: 1,

  actionAdd: function(e) {
    if (Multifields.el.classList.contains('mf-row-group')) {
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
        && (
            (
                !Multifields.el.parentElement.parentElement.classList.contains('mf-row-group')
                && Multifields.el.parentElement.querySelectorAll('.mf-row[data-name="' + Multifields.name + '"]').length === 1
            )
            ||
            (
                !Multifields.el.parentElement.parentElement.querySelector('.mf-templates [data-template-name="' + Multifields.name + '"]'
                )
            )
        )
    ) {
      if (Multifields.el.classList.contains('mf-row-group')) {
        Multifields.el.querySelector('.mf-items').innerHTML = '';
      } else {
        Multifields.getTemplate(function(data) {
          Multifields.el.insertAdjacentHTML('afterend', data.html);
          Multifields.element('row').deleteRow();
        });
      }
    } else {
      Multifields.element('row').deleteRow();
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

  template: function(id) {
    Multifields.getTemplate(id, function(data) {
      let template = document.createElement('template');
      template.innerHTML = data.html;
      Multifields.setDatepicker(template.content);
      Multifields.draggable(template.content.querySelectorAll(':scope > .mf-items, .mf-draggable > .mf-items'));
      Multifields.el.querySelector('.mf-items').appendChild(template.content);
    });
  },

  build: function(el, item, i) {
    if (el.dataset['autoincrement']) {
      item.autoincrement = el.dataset['autoincrement'];
    }
    return item;
  }
});