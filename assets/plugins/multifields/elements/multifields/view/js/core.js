!function(t) {
  'use strict';
  window.Multifields = t();
}(function() {
  'use strict';

  let __ = function() {
    this.container = null;
    this.el = null;
    this.name = null;

    document.addEventListener('click', function() {
      document.querySelectorAll('.multifields.open, .multifields .open').forEach(function(el) {
        el.classList.remove('open');
      });
    });

    document.addEventListener('DOMContentLoaded', function() {
      let form = document.getElementById('mutate');
      if (form) {
        form.addEventListener('submit', function() {
          document.querySelectorAll('.multifields').forEach(function(el) {
            Multifields.container = el;
            Multifields.build();
          });
          document.querySelectorAll('.multifields [name]').forEach(function(el) {
            el.disabled = true;
          });
        });
      }
      document.querySelectorAll('.multifields').forEach(function(el) {
        Multifields.init(el);
      });
      Multifields.draggable(document.querySelectorAll('.multifields .mf-items'));

      // delete all mm_widget_showimagetvs
      if (typeof jQuery !== 'undefined') {
        jQuery('.multifields [data-type="image"] > [type="text"]').off('change.mm_widget_showimagetvs load.mm_widget_showimagetvs');
        jQuery('[data-type="custom_tv:multifields"] > .tvimage').remove();
      }
    });
  };

  __.prototype = {
    elements: {},

    /**
     * Инициализация
     * @param el
     */
    init: function(el) {
      el.addEventListener('mousedown', function(e) {
        Multifields.container = el;
        Multifields.el = e.target.closest('[data-type]');
        Multifields.name = Multifields.el.dataset['name'];
        Multifields.type = Multifields.el.dataset['type'];
      });

      for (let k in Multifields.elements) {
        if (Multifields.elements.hasOwnProperty(k)) {
          Multifields.elements[k].init(el);
        }
      }
    },

    /**
     * Добавляем новый элемент и регистрируем его, дополняя его базовыми методами или возвращаем уже существующий
     * @param type
     * @param obj
     * @returns {*}
     */
    element: function(type, obj) {
      if (Multifields.elements[type]) {
        return Multifields.elements[type];
      } else {
        let __ = function() {
          for (let k in obj) {
            if (obj.hasOwnProperty(k)) {
              this[k] = obj[k];
            }
          }
        };
        __.prototype = {
          init: function() {},
          actionAdd: function() {
            Multifields.getTemplate(function(data) {
              Multifields.el.insertAdjacentHTML('afterend', data.html);
              Multifields.draggable(Multifields.el.nextElementSibling.querySelectorAll('.mf-draggable > .mf-items'));
              Multifields.setDatepicker(Multifields.el.nextElementSibling);
            });
          },
          actionDel: function() {
            Multifields.el.parentElement.removeChild(Multifields.el);
          },
          actionMove: function() {}
        };
        Multifields.elements[type] = new __();
      }
    },

    /**
     * Загружаем код шаблона по его названию, либо клониурем.
     * @param template
     * @param callback
     */
    getTemplate: function(template, callback) {
      if (typeof template === 'function') {
        callback = template;
        template = false;
      }
      Multifields.getAction({
        action: 'template',
        class: 'Multifields\\Base\\Elements',
        tpl: template || Multifields.name,
        tvid: Multifields.container.dataset['tvId'],
        tvname: Multifields.container.dataset['tvName']
      }, function(data) {
        if (typeof callback === 'function') {
          if (data.html) {
            callback.call(Multifields, data);
          } else {
            callback.call(Multifields, {
              html: Multifields.clone().outerHTML
            });
          }
        }
      });
    },

    /**
     * Клонируем элемент с очисткой данных
     * @param clear
     * @param clone
     * @returns {ActiveX.IXMLDOMNode|Node}
     */
    clone: function(clear, clone) {
      clear = typeof clear !== 'undefined' ? clear : true;
      clone = !clone ? Multifields.el.cloneNode(true) : clone.cloneNode(true);
      clone.style.backgroundImage = '';
      if (clear) {
        clone.querySelectorAll('[style^="background-image"]').forEach(function(el) {
          el.style.backgroundImage = '';
        });
      }
      clone.querySelectorAll('[name]').forEach(function(el) {
        let _id = el.id,
            id = Multifields.uniqid();
        if (el.name.substr(0, 2) === 'tv') {
          id = 'tv' + id.substr(2);
        }
        if (el.type === 'radio') {
          el.id = id;
          el.name = id;
        } else if (el.type === 'checkbox') {

        } else {
          el.id = id;
          el.name = id;
          if (clear && !el.readOnly) {
            el.innerHTML = '';
            el.value = '';
            el.setAttribute('value', '');
          }
          if (el.getAttribute('onclick')) {
            el.setAttribute('onclick', el.getAttribute('onclick').replace(new RegExp(_id, 'g'), id));
          }
          if (el.nextElementSibling && el.nextElementSibling.getAttribute('onclick')) {
            el.nextElementSibling.setAttribute('onclick', el.nextElementSibling.getAttribute('onclick').replace(new RegExp(_id, 'g'), id));
          }
        }
        clone.querySelectorAll('[for="' + _id + '"]').forEach(function(label) {
          label.setAttribute('for', id);
        });
      });
      return clone;
    },

    /**
     * Собираем данные
     */
    build: function() {
      if (Multifields.container) {
        let data = Multifields.buildItems(Multifields.container.querySelector('.mf-items').children);
        Multifields.container.nextElementSibling.value = data.length && JSON.stringify(data) || '';
      }
    },

    /**
     * Собираем данные из вложенных полей
     * @param els
     * @returns {[]}
     */
    buildItems: function(els) {
      let data = [];
      if (els) {
        for (let i = 0; i < els.length; i++) {
          if (els[i].tagName === 'DIV') {
            let item = Object.assign({}, els[i].dataset),
                el;
            if (item.type) {
              if (els[i].querySelector('.mf-items')) {
                el = els[i].querySelector(':scope > .mf-value input');
              } else {
                el = els[i].querySelectorAll('[name]');
              }
            } else {
              el = els[i].querySelector(':scope > input');
            }
            if (el) {
              if (el.length) {
                let value = [];
                [...el].map(function(el) {
                  if (!el.hidden && el.checked && (el.type === 'checkbox' || el.type === 'radio')) {
                    value.push(el.value);
                  }
                });
                item.value = value.join('||');
              } else {
                if (!el.hidden) {
                  item.value = el.value || el.innerHTML || '';
                }
                if (el.placeholder !== '') {
                  item.placeholder = el.placeholder;
                }
              }
            }
            item.items = els[i].querySelector('.mf-items');
            if (item.items && item.items.children.length) {
              if (Multifields.elements[item.type] && typeof Multifields.elements[item.type]['buildItems'] === 'function') {
                item.items = Multifields.elements[item.type]['buildItems'](els[i], item, i);
              } else {
                item.items = Multifields.buildItems(item.items.children);
              }
            } else {
              delete item.items;
            }
            if (Multifields.elements[item.type] && typeof Multifields.elements[item.type]['build'] === 'function') {
              item = Multifields.elements[item.type]['build'](els[i], item, i);
            }
            data.push(item);
          }
        }
      }
      return data;
    },

    /**
     * Уникальный ID
     * @returns {string}
     */
    uniqid: function() {
      return 'id' + (new Date()).getTime() + (Math.floor(Math.random() * (99999 + 1)));
    },

    /**
     * Обращаемся через аякс к методу класса
     * @param data
     * @param callback
     */
    getAction: function(data, callback) {
      if (!data.action) {
        return;
      }
      let xhr = new XMLHttpRequest();
      data = Object.keys(data).map(function(k) {
        return k + '=' + data[k];
      });
      data.push('mf-action=');
      xhr.open('POST', document.location.href.split('?')[0], true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
      xhr.setRequestHeader('X-REQUESTED-WITH', 'XMLHttpRequest');
      xhr.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
          let response = JSON.parse(this.response);
          if (response.error) {
            alert(response.error);
          } else {
            if (typeof callback === 'function') {
              callback(response);
            }
          }
        }
      };
      xhr.send(data.join('&'));
    },

    /**
     * Закрываем открытые элементы
     * @param ignore
     */
    closeOpened: function(ignore) {
      document.querySelectorAll('.multifields.open, .multifields .open').forEach(function(el) {
        if (ignore !== el) {
          el.classList.remove('open');
        }
      });
    },

    /**
     * Устанавливаем календарь для элементов с датой
     * @param el
     */
    setDatepicker: function(el) {
      if (el) {
        if (el.classList && el.classList.contains('DatePicker')) {
          new DatePicker(el, {
            yearOffset: dpOffset,
            format: el.dataset['format'] || dpformat,
            dayNames: dpdayNames,
            monthNames: dpmonthNames,
            startDay: dpstartDay
          });
        } else {
          el.querySelectorAll('.DatePicker').forEach(function(el) {
            new DatePicker(el, {
              yearOffset: dpOffset,
              format: el.dataset['format'] || dpformat,
              dayNames: dpdayNames,
              monthNames: dpmonthNames,
              startDay: dpstartDay
            });
          });
        }
      }
    },

    /**
     * Устанавливаем перетаксивание элементов
     * @param els
     * @returns {{length}|*}
     */
    draggable: function(els) {
      if (els.length) {
        els.forEach(function(el) {
          if (Multifields.elements[el.parentElement.dataset.type] && typeof Multifields.elements[el.parentElement.dataset.type]['draggable'] === 'function') {
            Multifields.elements[el.parentElement.dataset.type]['draggable'](el);
          } else {
            Sortable.create(el, {
              animation: 50,
              draggable: '.mf-draggable',
              dragClass: 'mf-drag',
              ghostClass: 'mf-active',
              selectedClass: 'mf-selected',
              handle: '.mf-actions-move'
            });
            if (el.classList.contains('mf-items-table')) {
              Sortable.create(el, {
                animation: 0,
                draggable: '.mf-draggable',
                dragClass: 'mf-drag',
                ghostClass: 'mf-active',
                selectedClass: 'mf-selected',
                handle: '.mf-actions-move',
                tableDisplay: parseInt(el.dataset['display']),
                onEnd: function(e) {
                  e.item.parentElement.querySelectorAll('[data-autoincrement]').forEach(function(item, i) {
                    let el = item.querySelector('[data-type="' + item.dataset['autoincrement'] + '"] > input');
                    if (el) {
                      el.value = i + 1;
                    }
                  });
                }
              });
            }
          }
        });
      }
      return els;
    }
  };

  return new __();
});
