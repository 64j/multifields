var Multifields = (function($, w, d) {
  Multifields = function() { };

  Multifields.prototype = {
    init: function(a) {
      if (!a) {
        return;
      }
      this.id = a.id;
      this.field_id = a.field_id;
      this.field_name = a.field_name;
      this.el = document.getElementById(this.field_id);
      this.el.complete = function() {
        s.oncomplete();
      };
      var s = this;
      this.wrap = document.getElementById('multifields_' + this.field_id);
      this.wrap.addEventListener('keyup', function() {
        s.oncomplete();
      });
      this.wrap.addEventListener('click', function(e) {
        if (e.target.classList.contains('mf-add')) {
          s.add.call(s, e);
        }
        if (e.target.classList.contains('mf-del')) {
          s.del.call(s, e);
        }
        if (typeof e.target.name !== 'undefined') {
          s.oncomplete();
        }
      });
      this.draggable();
    },
    oncomplete: function() {
      var s = this;
      this.counter = 0;
      this.array = JSON.stringify(this.build(this.wrap, 1));
      this.el.value = this.array === '{}' ? '' : this.array;
      //      documentDirty = true;
      if (typeof tinymce !== 'undefined') {
        [].forEach.call(s.wrap.querySelectorAll('.item-cell.richtext'), function(el) {
          var textarea = el.querySelector('textarea');
          if (tinymce.get(textarea.id)) {
            tinymce.execCommand('mceRemoveEditor', true, textarea.id);
          }
          tinymce.execCommand('mceAddEditor', false, textarea.id);
        });
      }
    },
    add: function(e) {
      var s = this, el = e.target, row, toolbar, group, tpl;
      if (el.parentNode.parentNode.classList.contains('group-toolbar')) {
        toolbar = el.parentNode.parentNode;
        tpl = toolbar.querySelector('select') || toolbar.querySelector('input');
        group = toolbar.parentNode;
        if (tpl && tpl.value) {
          this.loadTemplate(tpl.value, function(data) {
            if (data) {
              group.insertAdjacentHTML('beforeEnd', data);
              s.oncomplete();
            }
          });
        }
      } else {
        row = el.closest('.item-rows');
        if (row && row.dataset && row.dataset.tpl) {
          tpl = row.dataset.tpl.split('__');
          this.loadTemplate(tpl[0], function(data) {
            if (data) {
              row.insertAdjacentHTML('afterEnd', data);
              s.oncomplete();
            }
          });
        }
      }
    },
    del: function(e) {
      var s = this, el = e.target, els, row, group;
      row = el.closest('.item-rows');
      if (el.parentNode.parentNode.classList.contains('group-toolbar')) {
        if (row) {
          if (row.classList.contains('item-row-group')) {
            group = row.firstElementChild;
            if (group) {
              els = group.querySelectorAll('.item-rows');
              if (els.length && confirm('Delete all ?')) {
                for (var i = 0; i < els.length; i++) {
                  els[i].parentNode.removeChild(els[i]);
                }
              } else {
                if (!row.parentNode.classList.contains('item-section')) {
                  row.parentNode.removeChild(row);
                } else {
                  alert('Not deleted !');
                }
              }
            }
          } else {
            row.parentNode.removeChild(row);
          }
        } else {
          row = el.closest('.multifields');
          if (row) {
            els = row.querySelectorAll('.item-rows');
            if (els.length && confirm('Delete all ?')) {
              for (var i = 0; i < els.length; i++) {
                els[i].parentNode.removeChild(els[i]);
              }
            }
          }
        }
      } else {
        if (row) {
          //          els = row.querySelectorAll('.item-row');
          //          if (els && els.length === 1) {
          //            els = els[0].querySelectorAll('[name]');
          //            for (var i = 0; i < els.length; i++) {
          //              els[i].value = '';
          //            }
          //          } else {
          row.parentNode.removeChild(row);
          //          }
        }
      }
      s.oncomplete();
    },
    loadTemplate: function(tpl, callback) {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '../assets/tvs/multiFields/tv.ajax.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
      xhr.setRequestHeader('Content-Type', 'application/json');
      xhr.setRequestHeader('X-REQUESTED-WITH', 'XMLHttpRequest');
      xhr.onload = function() {
        if (this.readyState === 4) {
          if (typeof callback === 'function') {
            callback(this.response);
          }
          var a = [], b, c = /<script[^>]*>([\s\S]*?)<\/script>/gi;
          while ((b = c.exec(this.response))) {
            a.push(b[1]);
          }
          a = a.join('\n');
          if (a) {
            (w.execScript) ? w.execScript(a) : w.setTimeout(a, 0);
          }
          var DatePickers = d.querySelectorAll('input.DatePicker');
          if (DatePickers) {
            for (var i = 0; i < DatePickers.length; i++) {
              new DatePicker(DatePickers[i], {
                yearOffset: dpOffset, format: dpformat, dayNames: dpdayNames, monthNames: dpmonthNames, startDay: dpstartDay
              });
            }
          }
        }
      };
      xhr.send('field_id=' + this.id + '&field_name=' + this.field_name + '&template_name=' + tpl);
    },
    build: function(a, c) {
      var s = this,
          b = Object.create(null);
      c = c || 0;
      [].forEach.call(a.children, function(el, i) {
        var tpl, parent = el.parentNode, parentParent, _;
        if (!c && el.classList.contains('group-toolbar')) {
          var elTitle = el.querySelector('.item-group-title');
          if (elTitle.firstElementChild) {
            b.value = elTitle.firstElementChild.value;
          } else {
            b.value = elTitle.innerHTML;
          }
        }
        if (el.classList.contains('item-rows')) {
          if (el.classList.contains('item-row-group') && el.parentNode.classList.contains('item-section')) {
            if (typeof b === 'object') {
              if (!b.length || b.length === 0) {
                b = [];
              }
            }
            b.push(s.build(el));
          } else {
            if (!b.rows) {
              b.rows = [];
            }
            //            if (el.dataset.tpl) {
            //              b.tpl = el.dataset.tpl;
            //            }
            b.rows.push(s.build(el));
          }
        }
        if (el.classList.contains('item-group')) {
          tpl = parent.dataset && parent.dataset.tpl ? ':' + parent.dataset.tpl : '';
          if (!b['group' + tpl]) {
            b['group' + tpl] = {};
          }
          $.extend(b['group' + tpl], s.build(el));
        }
        if (el.classList.contains('item-section')) {
          tpl = parent.dataset && parent.dataset.tpl ? ':' + parent.dataset.tpl : '';
          if (!b['section' + tpl]) {
            b['section' + tpl] = {rows: s.build(el)};
          }
        }
        if (el.classList.contains('item-row')) {
          if (parent.classList.contains('item-rows')) {
            b = s.build(el);
          } else {
            if (typeof b === 'object') {
              if (!b.length || b.length === 0) {
                b = [];
              }
            }
            b.push(s.build(el));
          }
        }
        if (el.classList.contains('item-cell')) {
          parentParent = parent.parentNode;
          if (parentParent && parentParent.dataset.tpl
              && !parentParent.classList.contains('item-section')
              && (el.parentNode.dataset && el.parentNode.dataset.name && el.parentNode.dataset.name !== 'items')
          ) {
            //b.tpl = parentParent.dataset.tpl;
            tpl = ':' + parentParent.dataset.tpl;
          } else {
            tpl = '';
          }
          var item = el.querySelector('[name]');
          if (item) {
            var _ = item.name.split('__') || item.id.split('__'),
                value = item.value,
                type = _[2].replace('[]', ''),
                name = (_[3].replace('[]', '') || _[1].replace('[]', '')) + tpl;
            if (item.nodeName === 'DIV') {
              value = item.innerHTML;
            }
          }
          switch (type) {
            case 'checkbox':
            case 'option': {
              var els = el.querySelectorAll('[name]:checked');
              var values = [];
              [].forEach.call(els, function(el) {
                values.push(el.value);
              });
              value = values.join('||');
              break;
            }
          }
          if (parent.dataset && parent.dataset.name && parent.dataset.name === 'items') {
            parentParent = parent.parentNode;
            tpl = parentParent.dataset && parentParent.dataset.tpl && !parentParent.classList.contains('item-section')  ? ':' + parentParent.dataset.tpl : '';
            if (!b['items' + tpl]) {
              b['items' + tpl] = [];
            }
            if (el.firstElementChild.classList.contains('item-row')) {
              _ = {'rows': s.build(el)};
            } else if (el.firstElementChild.classList.contains('item-rows')) {
              _ = s.build(el.firstElementChild);
            } else {
              _ = Object.create(null);
              _[name] = value;
            }
            b['items' + tpl].push(_);
          } else {
            if (el.children[1] && el.children[1].classList.contains('item-rows')) {
              b[name] = {'value': el.children[0].value, 'rows': []};
              [].forEach.call(el.children, function(a) {
                if (a.classList.contains('item-rows')) {
                  b[name]['rows'].push(s.build(a));
                }
              });
            } else {
              b[name] = value;
            }
          }
        }
      });
      return b;
    },
    draggable: function() {
      var s = this, placeholder;
      s.wrap.addEventListener('mousedown', function(e) {
            if (!e.target.classList.contains('mf-move')) {
              return;
            }
            var el,
                drag = e.target.closest('.draggable'),
                parent = drag.parentNode,
                marginTop = parseInt(w.getComputedStyle(drag).marginTop),
                marginLeft = parseInt(w.getComputedStyle(drag).marginLeft),
                posX = e.pageX,
                posY = e.pageY;
            drag.classList.add('active');
            s.wrap.classList.add('dragging');
            placeholder = drag.cloneNode(true);
            placeholder.innerHTML = '';
            placeholder.classList.add('placeholder');
            placeholder.classList.remove('draggable');
            //drag.position = drag.getBoundingClientRect();
            drag.position = {
              left: drag.offsetLeft,
              top: drag.offsetTop
            };
            drag.style.left = (drag.position.left - marginLeft) + 'px';
            drag.style.top = (drag.position.top - marginTop) + 'px';
            drag.style.width = placeholder.style.width = drag.offsetWidth + 'px';
            drag.style.height = placeholder.style.height = drag.offsetHeight + 'px';
            drag.style.position = 'absolute';
            parent.insertBefore(placeholder, drag);
            parent.appendChild(drag);
            d.addEventListener('mousemove', onmousemove);
            d.addEventListener('mouseup', onmouseup);
            d.addEventListener('mousedown', disableSelection);
            w.addEventListener('blur', onmouseup);
            e.preventDefault();
            e.stopPropagation();

            function disableSelection()
            {
              if (w.getSelection) {
                w.getSelection().removeAllRanges();
              } else {
                d.selection.empty();
              }
              return false;
            }

            function onmousemove(e)
            {
              if (e.which === 1) {
                var x = (e.pageX - posX) + drag.position.left - marginLeft,
                    y = (e.pageY - posY) + drag.position.top - marginTop;
                drag.style.pointerEvents = 'none';
                el = d.elementFromPoint(e.clientX, e.clientY);
                if (el && el.classList.contains('draggable') && el.parentNode === drag.parentNode) {
                  if (!el.classList.contains('float')) {
                    if (el.offsetTop + (el.offsetHeight / 2) > y + (!drag.classList.contains('float') ? (el.offsetHeight / 2) : 0)) {
                      $(el).before(placeholder);
                    } else {
                      $(el).after(placeholder);
                    }
                  } else {
                    if (el.offsetLeft + (el.offsetWidth / 2) > x) {
                      $(el).before(placeholder);
                    } else {
                      $(el).after(placeholder);
                    }
                  }
                }
                drag.style.pointerEvents = 'all';
                drag.style.left = x + 'px';
                drag.style.top = y + 'px';
              } else {
                onmouseup(e);
              }
            }

            function onmouseup(e)
            {
              d.removeEventListener('mousemove', onmousemove);
              d.removeEventListener('mouseup', onmouseup);
              d.removeEventListener('mousedown', disableSelection);
              w.removeEventListener('blur', onmouseup);
              $(drag).animate({
                top: placeholder.offsetTop - marginTop,
                left: placeholder.offsetLeft
              }, 100, function() {
                placeholder.parentNode.insertBefore(drag, placeholder);
                placeholder.parentNode.removeChild(placeholder);
                s.oncomplete();
                drag.classList.remove('active');
                drag.removeAttribute('style');
                $('.hover', s.wrap).removeClass('hover');
                s.wrap.classList.remove('dragging');
              });
              e.preventDefault();
              e.stopPropagation();
            }

          }
      );
    },
    openRTEinWindow: function(id, tvID) {
      var multiFieldsOpenRTEinWindow;
      var url = '../assets/tvs/multiFields/tv.richtext.php';

      if (parent.modx) {
        multiFieldsOpenRTEinWindow = parent.modx.popup({
          url: url,
          iframe: 'iframe',
          overlay: 1,
          hover: 0,
          hide: 0,
          width: '95%',
          height: '95%',
          margin: 0,
          resize: 0,
          draggable: 0,
          title: 'MultiFields:: RichText'
        });

        multiFieldsOpenRTEinWindow.frame.addEventListener('load', function() {
          var w = this.contentWindow;
          var form = w.document.getElementById('ta_form');
          var textarea = w.document.getElementById('ta');
          textarea.value = document.getElementById(id).value;
          w.tinyMCE.get('ta').remove();
          w.tinyMCE.execCommand('mceAddEditor', false, 'ta');
          form.addEventListener('submit', function(e) {
            textarea = this.querySelector('textarea#ta');
            setTimeout(function() {
              document.getElementById(id).value = textarea.value;
              document.getElementById(tvID).complete();
              w.documentDirty = false;
              multiFieldsOpenRTEinWindow.close();
            }, 200);
            e.preventDefault();
          }, false);
        }, false);
      } else {
        alert('parent.modx not found !');
      }
    },
    changeThumbs: function(tvID, el) {
      if (el.dataset && el.dataset.thumb) {
        var els = document.querySelectorAll('#thumb' + el.dataset.thumb);
        [].forEach.call(els, function(a) {
          if (a.previousElementSibling && a.previousElementSibling.classList.contains('col-item-thumb')) {
            a = a.previousElementSibling;
          }
          if (el.value) {
            a.style.backgroundImage = 'url(../' + el.value + ')';
          } else {
            a.removeAttribute('style');
          }
        });
        els = document.querySelectorAll('#tv' + el.dataset.thumb);
        [].forEach.call(els, function(a) {
          a.value = el.value;
          a.setAttribute('value', el.value);
        });
        els = document.querySelectorAll('#' + el.id);
        [].forEach.call(els, function(a) {
          a.value = el.value;
          a.setAttribute('value', el.value);
        });
        document.getElementById(tvID).complete();
      }
    },
    changeThumb: function(tvID, el) {
      if (el.parentNode.dataset && el.parentNode.dataset.type) {
        if (el.parentNode.dataset.type === 'image') {
          if (el.value) {
            el.parentNode.style.backgroundImage = 'url(../' + el.value + ')';
          } else {
            el.parentNode.removeAttribute('style');
          }
        }/* else if (el.parentNode.dataset.type === 'file') {
          if (el.value) {
            var ext = el.value.split('.');
            ext = ext[ext.length - 1];
            el.parentNode.style.backgroundImage = 'url(media/browser/mcpuk/themes/oxygen/img/files/big/' + ext + '.png)';
          } else {
            el.parentNode.removeAttribute('style');
          }
        }*/
      }
      document.getElementById(tvID).complete();
    },
    openThumbWindow: function(e, tvID, el) {
      var multiFieldsOpenThumbWindow;
      if (parent.modx) {
        multiFieldsOpenThumbWindow = parent.modx.popup({
          width: '70%',
          height: 'auto',
          hide: 0,
          hover: 0,
          overlay: 1,
          overlayclose: 1,
          content: '<div class="multifields table">' +
              '<div class="col-item-thumb item-thumb"' + (el.style.backgroundImage ? ' style=\'background-image:' + el.style.backgroundImage + '\'' : '') + '></div>' +
              '<div class="col-item-thumb-rows" id="' + el.id + '">' + el.innerHTML + '</div>' +
              '</div>' +
              '<div class="btn btn-success btn-block" onclick="Multifields.prototype.saveThumbWindow(\'' + el.id + '\',\'' + tvID + '\',this.parentNode.parentNode)">Ok</div>'
        });
        multiFieldsOpenThumbWindow.el.onchange = function(e) {
          if (e.target.dataset && e.target.dataset.thumb) {
            Multifields.prototype.changeThumbs(tvID, e.target);
          }
        };
        multiFieldsOpenThumbWindow.el.onkeyup = function(e) {
          if (e.target.dataset && e.target.dataset.thumb) {
            Multifields.prototype.changeThumbs(tvID, e.target);
          }
        };
      } else {
        alert('parent.modx not found !');
      }
      e.preventDefault();
    },
    saveThumbWindow: function(id, tvID, el) {
      var openThumb = document.querySelector('#' + id + '.col-item-thumb-rows');
      var inputs = openThumb.querySelectorAll('[name]');
      var thumb = document.querySelector('#' + id + '.thumb-item-rows');
      [].forEach.call(inputs, function(a) {
        var b = thumb.getElementById(a.id);
        if (b) {
          b.value = a.value;
          if (b.nodeName === 'INPUT') {
            b.setAttribute('value', a.value);
          } else if (b.nodeName === 'TEXTAREA') {
            b.innerHTML = a.value;
          }
        }
      });
      document.getElementById(tvID).complete();
      el.close();
    }
  };

  return Multifields;
})
(jQuery, window, document);
