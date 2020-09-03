Multifields.element('multifields', {

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
  }
});