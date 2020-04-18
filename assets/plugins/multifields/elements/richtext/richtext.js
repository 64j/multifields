Multifields.element('richtext', {
  class: 'Multifields\\Elements\\Richtext\\Richtext',
  popup: null,

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
  }
});
