Multifields.element('richtext', {
  popup: null,

  init: function() {
    [...document.querySelectorAll('.mf-richtext-inline')].map(function(el) {
      Multifields.elements.richtext.initEl(el);
    });
  },

  initEl: function(el, init) {
    let theme = el.dataset['mfTheme'] ? el.dataset['mfTheme'] : '',
        options = el.dataset['mfOptions'] ? JSON.parse(el.dataset['mfOptions']) : {};
    if (options.init || init) {
      let inputEl = el.querySelector('textarea');
      if (typeof tinymce !== 'undefined') {
        let conf = theme !== undefined ? window['config_tinymce4_' + theme] : window[modxRTEbridge_tinymce4.default];
        conf = Object.assign({}, conf, options);
        conf.selector = '#' + inputEl.id;
        tinymce.init(conf);
      } else if (typeof myCodeMirrors !== 'undefined') {
        if (myCodeMirrors['ta']) {
          options = Object.assign({}, myCodeMirrors['ta'].options, options);
        }
        myCodeMirrors[inputEl.id] = CodeMirror.fromTextArea(inputEl, options);
      }
    }
  },

  initEls: function(el, init) {
    [...el.querySelectorAll('.mf-richtext-inline')].map(function(el) {
      Multifields.elements.richtext.initEl(el, init);
    });
  },

  actionDisplay: function() {
    if (parent.modx) {
      Multifields.elements.richtext.popup = parent.modx.popup({
        iframe: 'iframe',
        height: '85%',
        width: '85%',
        draggable: 0,
        showclose: 0,
        overlay: 1,
        margin: 0,
        resize: 0,
        delay: 0,
        hover: 0,
        hide: 0,
        url: '?mf-action=&class=' + this.class + '&action=display',
        onclose: function(e, el) {
          el.classList.remove('show');
          Multifields.elements.richtext.popup = null;
        }
      });

      Multifields.elements.richtext.popup.frame.onload = function() {
        parent.modx.main.stopWork();

        let w = this.contentWindow,
            form = w.document.getElementById('ta_form'),
            textarea = w.document.getElementById('ta'),
            editor = w.tinymce && w.tinymce.get('ta');

        textarea.value = Multifields.el.querySelector('textarea').value;

        if (editor) {
          editor.setContent(textarea.value);
        }

        form.querySelector('#actions .btn-close').onclick = function() {
          w.documentDirty = false;
          Multifields.elements.richtext.popup.close();
        };

        form.onsubmit = function(e) {
          e.preventDefault();
          setTimeout(function() {
            w.documentDirty = false;
            Multifields.el.querySelector('textarea').value = textarea.value;
            Multifields.elements.richtext.popup.close();
          }, 100);
        };
      };
    } else {
      alert('Not found function parent.modx !');
    }
  },

  build: function(el, item, i) {
    let id = el.querySelector('textarea').id;
    if (typeof tinymce !== 'undefined') {
      if (tinymce.editors[id]) {
        item.value = tinymce.editors[id].getContent();
      }
    } else if (typeof myCodeMirrors !== 'undefined') {
      if (myCodeMirrors[id]) {
        item.value = myCodeMirrors[id].getValue();
      }
    }
    return item;
  },

  destroy: function(el) {
    if (typeof tinymce !== 'undefined' && tinymce.editors[el.id]) {
      el.value = tinymce.editors[el.id].getContent();
      tinymce.editors[el.id].destroy();
    } else if (typeof myCodeMirrors !== 'undefined') {
      if (myCodeMirrors[el.id]) {
        el.value = myCodeMirrors[el.id].getValue();
        myCodeMirrors[el.id].toTextArea();
      }
    }
  },

  destroyEls: function(el) {
    [...el.querySelectorAll('.mf-richtext-inline')].map(function(el) {
      Multifields.elements.richtext.destroy(el.querySelector('textarea'));
    });
  }
});
