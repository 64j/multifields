!function(t) {
  'use strict';
  window.MultiFields = t();
}(function() {
  'use strict';

  function __()
  {
    var self = this;
    this.el = {};
    this.data = {};
    document.mutate.addEventListener('submit', function() {
      self.build();
    });
    document.addEventListener('DOMContentLoaded', function() {
      var els = document.querySelectorAll('.multifields');
      els.forEach(function(el) {
        self.init(el);
      });
      self.draggable(els);
    });
    if (typeof window.SetUrlChange === 'undefined') {
      window.SetUrlChange = this.SetUrlChange;
    }
    if (typeof window.SetUrl === 'undefined') {
      window.SetUrl = this.SetUrl;
    }
  }

  __.prototype = {
    constructor: __,
    init: function(el) {
      var self = this, id = el.getAttribute('data-tvid');
      this.el[id] = el;
      this.data[id] = el.nextElementSibling;
      el.addEventListener('click', function(e) {
        var target = e.target, el, parent = target.parentElement.parentElement;
        if (target.classList.contains('mf-actions-add')) {
          self.clone(parent);
        }
        if (target.classList.contains('mf-actions-del')) {
          parent.parentElement.removeChild(parent);
        }
        if (target.classList.contains('mf-actions-edit-image')) {
          el = parent.querySelector('[data-value]');
          el.onchange = function() {
            parent.style.backgroundImage = 'url(/' + el.value + ')';
          };
          self.BrowseServer(el.id, 'images', parent.getAttribute('data-multi'));
        }
        if (target.classList.contains('mf-toolbar-add')) {
          var _t = target.previousElementSibling.getBoundingClientRect().top,
              _h = target.previousElementSibling.offsetHeight;
          if (_t - _h < 70) {
            target.parentElement.classList.remove('mf-toolbar-wrap-top');
          } else if (!target.parentElement.classList.contains('mf-toolbar-wrap-top') && _t + _h > 70) {
            target.parentElement.classList.add('mf-toolbar-wrap-top');
          }
          if (target.previousElementSibling.childElementCount === 1) {
            target.previousElementSibling.firstElementChild.click();
          }
        }
        if (target.classList.contains('mf-option')) {
          self.getTemplate(id, target.getAttribute('data-id'), function(data) {
            data = JSON.parse(data);
            if (data['template']) {
              parent.parentElement.insertAdjacentHTML('beforeend', data['template']);
              if (parent.parentElement.lastElementChild.querySelector('[data-type="date"]')) {
                self.datePickersInit();
              }
              self.draggable(parent.parentElement.querySelectorAll('.mf-section, .mf-group, .mf-row'));
              var s = [], _s = '', b, c = /<script[^>]*>([\s\S]*?)<\/script>/gi;
              while ((b = c.exec(data['template']))) {
                s.push(b[1]);
              }
              _s = s.join('\n');
              if (_s) {
                /** @namespace window.execScript */
                (window.execScript) ? window.execScript(_s) : window.setTimeout(_s, 0);
              }
            }
          });
        }
      });
      el.querySelectorAll('[data-thumb]').forEach(function(item) {
        item.onchange = self.setThumbImage;
      });
      self.draggable(el.querySelectorAll('.mf-section, .mf-group, .mf-row'));
    },
    build: function() {
      var data, parent;
      for (var k in this.el) {
        if (this.el.hasOwnProperty(k)) {
          data = {};
          this.el[k].querySelectorAll('[data-name]').forEach(function(el, index, els) {
            parent = 0;
            for (var i in els) {
              if (els[i] === el.parentElement) {
                parent = parseInt(i) + 1;
                break;
              }
            }
            index++;
            data[index] = {
              parent: '' + parent,
              name: el.getAttribute('data-name')
            };
            if (el.classList.contains('mf-group')) {
              data[index]['value'] = el.querySelector('[data-value]').value;
            }
            if (el.classList.contains('mf-col')) {
              var _val = [], _separator = '', _els = el.querySelectorAll('[data-value]');
              _els.forEach(function(item) {
                if (item.type === 'checkbox' || item.type === 'radio') {
                  if (item.checked) {
                    _val.push(item.value);
                  }
                  _separator = '||';
                } else {
                  _val.push(item.value);
                }
              });
              data[index]['value'] = _val.join(_separator);
            }
          });
          this.data[k].value = Object.keys(data).length && JSON.stringify(data) || '';
        }
      }
    },
    clone: function(el, replace) {
      var self = this, clone = el.cloneNode(true);
      replace = replace || {};
      clone.querySelectorAll('[data-value]').forEach(function(el) {
        el.value = '';
        if (el.id) {
          if (typeof replace[el.id] !== 'undefined') {
            el.name = el.id = replace[el.id];
          } else {
            el.name = el.id = self.uniqid();
          }
        }
      });
      if (clone.classList.contains('mf-thumb')) {
        clone.style.backgroundImage = '';
      }
      clone.querySelectorAll('.mf-thumb').forEach(function(el) {
        el.style.backgroundImage = '';
      });
      el.insertAdjacentElement('afterend', clone);
      if (el.querySelector('[data-type="date"]')) {
        this.datePickersInit();
      }
      return clone;
    },
    draggable: function(els) {
      if (els.length) {
        els.forEach(function(el) {
          Sortable.create(el, {
            animation: 150,
            draggable: '.mf-draggable',
            dragClass: 'mf-drag',
            ghostClass: 'mf-active',
            selectedClass: 'mf-selected',
            //filter: '.b-settings, .b-tab-title-input, .b-btn-settings, .b-btn-add',
            preventOnFilter: false,
            handle: '.mf-actions-move'
          });
        });
      }
      return els;
    },
    getTemplate: function(tvId, tpl, callback) {
      var url = document.location.href.split('?')[0] + '?mf-action=template&tpl=' + tpl + '&tvid=' + tvId, xhr = new XMLHttpRequest();
      xhr.open('POST', url, true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
      xhr.setRequestHeader('X-REQUESTED-WITH', 'XMLHttpRequest');
      xhr.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
          callback(this.response);
        }
      };
      xhr.send();
    },
    datePickersInit: function() {
      if (typeof window['DatePickers'] !== 'undefined') {
        DatePickers = document.querySelectorAll('input.DatePicker');
        if (DatePickers) {
          for (var i = 0; i < DatePickers.length; i++) {
            var format = DatePickers[i].getAttribute('data-format');
            new DatePicker(DatePickers[i], {
              yearOffset: dpOffset,
              format: format !== null ? format : dpformat,
              dayNames: dpdayNames,
              monthNames: dpmonthNames,
              startDay: dpstartDay
            });
          }
        }
      }
    },
    getRichText: function(e, tvId, options) {
      options = options && '&options=' + options || '';
      var id = e.target.parentElement.querySelector('textarea').id,
          _richtext, url = document.location.href.split('?')[0] + '?mf-action=richtext' + options;
      if (parent.modx) {
        _richtext = parent.modx.popup({
          iframe: 'iframe',
          height: '85%',
          width: '85%',
          draggable: 0,
          showclose: 0,
          overlay: 1,
          margin: 0,
          resize: 0,
          hover: 0,
          hide: 0,
          url: url
        });
        _richtext.frame.addEventListener('load', function() {
          var w = this.contentWindow;
          var form = w.document.getElementById('ta_form');
          var textarea = w.document.getElementById('ta');
          textarea.value = document.getElementById(id).value;
          if (typeof w.tinymce !== 'undefined') {
            w.tinymce.get('ta').remove();
            w.tinymce.execCommand('mceAddEditor', false, 'ta');
          }
          form.onsubmit = function(e) {
            e.preventDefault();
            textarea = this.querySelector('textarea#ta');
            setTimeout(function() {
              w.documentDirty = false;
              document.getElementById(id).value = textarea.value;
              _richtext.close();
            }, 100);
          };
          form.querySelector('#actions .btn-close').onclick = function() {
            w.documentDirty = false;
            _richtext.close();
          };
        }, false);
      } else {
        alert('parent.modx not found !');
      }
    },
    BrowseServer: function(last, type, multi, thumb) {
      var self = this, o = '';
      window.lastFileCtrl = last;
      type = type || 'images';
      multi = multi || '';
      thumb = thumb || '';
      o += 'toolbar=no,status=no,resizable=yes,dependent=yes';
      o += ',width=' + screen.width / 2;
      o += ',height=' + screen.height / 2;
      o += ',left=' + screen.width / 4;
      o += ',top=' + screen.height / 4;
      window.open(MultiFields_urlBrowseServer + '?type=' + type, 'FCKBrowseWindow', o);
      window.KCFinder = {};
      if (thumb !== '') {
        document.getElementById(last).onchange = self.setThumbImage;
      }
      if (multi !== '') {
        var _interval = setInterval(function() {
          if (window.KCFinder) {
            clearInterval(_interval);
            window.KCFinder.callBackMultiple = function(files) {
              window.KCFinder = null;
              var el, thumbEl, parent = document.getElementById(last).closest('[data-name="' + multi + '"]');
              window.lastFileCtrl = last;
              window.SetUrl(files[0]);
              for (var k in files) {
                if (files.hasOwnProperty(k) && k !== '0') {
                  var r = {}, n = self.uniqid();
                  window.lastFileCtrl = r[last] = n;
                  parent = self.clone(parent, r);
                  window.SetUrl(files[k]);
                  if (parent.classList.contains('mf-image')) {
                    parent.style.backgroundImage = 'url(/' + files[k] + ')';
                  } else {
                    thumbEl = document.getElementById(n).closest('.mf-thumb');
                    if (thumbEl) {
                      thumbEl.style.backgroundImage = 'url(/' + files[k] + ')';
                    }
                    document.getElementById(n).onchange = self.setThumbImage;
                  }
                  if (thumb !== '') {
                    thumbEl = document.getElementById(n).closest('.mf-parent');
                    el = thumbEl.querySelector('[data-name="' + thumb + '"] [data-value]');
                    el.value = files[k];
                    el.parentElement.style.backgroundImage = 'url(/' + el.value + ')';
                    document.getElementById(n).onchange = self.setThumbImage;
                  }
                  last = n;
                }
              }
            };
          }
        }, 100);
      }
    },
    setThumbImage: function(e) {
      var parent = this.closest('.mf-parent'),
          el = parent.querySelector('[data-name="' + e.target.parentElement.getAttribute('data-thumb') + '"] [data-value]') || null;
      if (el) {
        el.value = e.target.value;
        el.parentElement.style.backgroundImage = 'url(/' + el.value + ')';
      }
    },
    SetUrlChange: function(el) {
      if ('createEvent' in document) {
        var evt = document.createEvent('HTMLEvents');
        evt.initEvent('change', false, true);
        el.dispatchEvent(evt);
      } else {
        el.fireEvent('onchange');
      }
    },
    SetUrl: function(url, width, height, alt) {
      var c;
      if (lastFileCtrl) {
        c = document.getElementById(lastFileCtrl);
        if (c && c.value !== url) {
          c.value = url;
          SetUrlChange(c);
        }
        lastFileCtrl = '';
      } else if (lastImageCtrl) {
        c = document.getElementById(lastImageCtrl);
        if (c && c.value !== url) {
          c.value = url;
          SetUrlChange(c);
        }
        lastImageCtrl = '';
      } else {
        return;
      }
    },
    uniqid: function() {
      return 'id' + (new Date()).getTime() + Math.random().toString(8).slice(2);
    }
  };

  return new __();
});
