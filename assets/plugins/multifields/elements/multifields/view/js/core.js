!function(t) {
  'use strict';
  window.Multifields = t();
}(function() {
  'use strict';

  return new function() {

    document.addEventListener('DOMContentLoaded', function() {
      Multifields.init();
    });

    return {
      container: null,
      name: null,
      el: null,

      /**
       * Инициализация
       */
      init: function() {

        let form = document['mutate'] || document['settings'];
        if (form) {
          form.addEventListener('submit', function() {
            [...document.querySelectorAll('.multifields')].map(function(el) {
              Multifields.container = el;
              if (!el.disabled) {
                Multifields.build();
              }
            });
            [...document.querySelectorAll('.multifields [name]')].map(function(el) {
              el.disabled = true;
            });
          });
        }

        document.addEventListener('click', function() {
          [...document.querySelectorAll('.multifields.open, .multifields .open')].map(function(el) {
            el.classList.remove('open');
          });
        });

        for (let k in Multifields.elements) {
          if (Multifields.elements.hasOwnProperty(k)) {
            Multifields.elements[k].init();
          }
        }

        Multifields.draggable(document.querySelectorAll('.multifields .mf-items'));

        // delete all mm_widget_showimagetvs
        if (typeof jQuery !== 'undefined') {
          jQuery('.multifields [data-type="image"] > [type="text"]').off('change.mm_widget_showimagetvs load.mm_widget_showimagetvs');
          jQuery('[data-type="custom_tv:multifields"] > .tvimage').remove();
        }
      },

      elements: {
        elements: {
          class: 'Multifields\\Base\\Elements',

          init: function() {},

          initEl: function() {},

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

          actionMove: function() {},

          onmousedown: function() {}
        }
      },

      /**
       * Добавляем новый элемент и регистрируем его, дополняя его базовыми методами или возвращаем уже существующий
       * @param type
       * @param obj
       * @returns {*}
       */
      element: function(type, obj) {
        if (!Multifields.elements[type]) {
          Multifields.elements[type] = new function() {
            return Object.assign({}, Multifields.elements.elements, {
              class: 'Multifields\\Elements\\' + (type + ':' + type).split(':', 2).map(function(str) {
                return str[0].toUpperCase() + str.slice(1).toLowerCase();
              }).join('\\')
            }, obj || {});
          };
        }
        return Multifields.elements[type];
      },

      /**
       * Загружаем код шаблона по его названию, либо клониурем.
       * @param template
       * @param callback
       * @param isTemplate
       */
      getTemplate: function(template, callback, isTemplate) {
        if (typeof template === 'function') {
          isTemplate = callback;
          callback = template;
          template = false;
        }
        template = template || Multifields.name;

        Multifields.getAction({
          action: 'template',
          class: Multifields.elements[Multifields.type] && Multifields.elements[Multifields.type].class || Multifields.elements.elements.class,
          tpl: template,
          tvid: Multifields.container.dataset['tvId'],
          tvname: Multifields.container.dataset['tvName']
        }, function(data) {
          if (typeof callback === 'function') {
            if (Multifields.checkLimit(template, true, isTemplate)) {
              return;
            }
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
        clone.id = clone.id && Multifields.uniqid() || '';
        if (clear) {
          [...clone.querySelectorAll('[style^="background-image"]')].map(function(el) {
            el.style.backgroundImage = '';
          });
        }
        [...clone.querySelectorAll('[name]')].map(function(el) {
          let _id = el.id,
              id = Multifields.uniqid();
          if (el.name.substr(0, 2) === 'tv') {
            id = 'tv' + id.substr(2);
          }
          if (el.type === 'radio') {
            el.id = id;
            el.name = id;
          } else if (el.type === 'checkbox') {

          } else if ((el.type === 'select-one' || el.type === 'select-multiple') && el.children.length) {
            el.id = id;
            el.name = id;
            el.children[0].selected = true;
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
          [...clone.querySelectorAll('[for="' + _id + '"]')].map(function(label) {
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
          Multifields.container.nextElementSibling.value = Object.values(data).length && JSON.stringify(data) || '';
        }
      },

      /**
       * Собираем данные из вложенных полей
       * @param els
       * @returns {[]}
       */
      buildItems: function(els) {
        let data = {},
            counters = {};
        if (els) {
          for (let i = 0; i < els.length; i++) {
            if (els[i].tagName === 'DIV') {
              let item = Object.assign({}, els[i].dataset),
                  el;
              if (!counters[item.name]) {
                counters[item.name] = 0;
              }
              counters[item.name]++;
              if (item.type) {
                if (els[i].querySelector('.mf-items')) {
                  el = els[i].querySelector(':scope > .mf-value input');
                } else {
                  el = els[i].querySelectorAll('[id][name]');
                }
              } else {
                el = els[i].querySelector(':scope > input');
              }
              if (el) {
                if (el.length) {
                  let value = [];
                  [...el].map(function(el) {
                    if (!el.hidden) {
                      switch (el.type) {
                        case 'checkbox':
                        case 'radio':
                          if (el.checked) {
                            value.push(el.value);
                          }
                          break;

                        case 'select':
                        case 'select-multiple':
                          for (let i = 0; i < el.length; i++) {
                            if (el[i].selected) {
                              value.push(el[i].value || el[i].text);
                            }
                          }
                          break;

                        default:
                          value.push(el.value || '');
                      }
                    }
                  });
                  item.value = value.join('||');
                } else {
                  if (!el.hidden) {
                    item.value = el.value || '';
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
              for (let i in item) {
                if (item.hasOwnProperty(i)) {
                  let ii = i.replace(/([a-z])([A-Z])/g, '$1-$2').replace('mf-', 'mf.').toLowerCase();
                  if (ii !== i) {
                    item[ii] = item[i];
                    delete item[i];
                  }
                }
              }
              if (!data[item.name] && !data[item.name + '#1']) {
                data[item.name] = item;
              } else {
                if (data[item.name]) {
                  let new_data = {};
                  for (let i in data) {
                    if (i !== item.name) {
                      new_data[i] = data[i];
                    } else {
                      new_data[item.name + '#1'] = data[item.name];
                    }
                  }
                  data = new_data;
                }
                data[item.name + (counters[item.name] && '#' + counters[item.name] || '')] = item;
              }
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
        [...document.querySelectorAll('.multifields.open, .multifields .open')].map(function(el) {
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
            [...el.querySelectorAll('.DatePicker')].map(function(el) {
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
       * Устанавливаем перетаскивание элементов
       * @param els
       * @returns {{length}|*}
       */
      draggable: function(els) {
        if (els.length) {
          [...els].map(function(el) {
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
                    [...e.item.parentElement.querySelectorAll('[data-autoincrement]')].map(function(item, i) {
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
      },

      /**
       * Проверяем лимиты
       * @param {*} name
       * @param {boolean} a
       * @param isTemplate
       */
      checkLimit: function(name, a, isTemplate) {
        let els = [],
            c,
            d;
        if (Multifields.el === Multifields.container || isTemplate) {
          els = Multifields.el.querySelectorAll(':scope > .mf-items > [data-name="' + name + '"]');
        } else {
          els = Multifields.el && Multifields.el.parentElement.querySelectorAll(':scope > [data-name="' + name + '"]');
        }
        c = !!(els.length && els[0].dataset.limit && els[0].dataset.limit <= els.length);
        if (c && a) {
          d = 'The limit (' + els[0].dataset.limit + ') for adding elements of this name has been exceeded.';
          if (parent.modx) {
            parent.modx.popup({
              type: 'warning',
              title: 'Multifields',
              position: 'top center alertMultifields',
              content: d,
              wrap: 'body'
            });
          } else {
            alert(d);
          }
        }
        return c;
      },

      /**
       * Работа с куками
       */
      cookie: {
        set: function(name, value, days) {
          let expires = '';
          if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
          }
          document.cookie = name + '=' + (value || '') + expires + '; path=/';
        },

        get: function(name) {
          let nameEQ = name + '=',
              ca = document.cookie.split(';');
          for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') {
              c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
          }
          return null;
        },

        del: function(name) {
          document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        }
      }
    };
  };
});
