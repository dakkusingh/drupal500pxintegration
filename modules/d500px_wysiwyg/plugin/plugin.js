/**
 * @file
 * The JavaScript file for the wysiwyg integration.
 */

(function ($) {

  /**
   * A CKEditor plugin for 500px filter
   */
  CKEDITOR.plugins.add('d500px_wysiwyg', {
    icons: '',
    hidpi: true,

    /**
     * Set the plugin modes.
     */
    modes: {
      wysiwyg: 1
    },

    /**
     * Define the plugin requirements.
     */
    requires: 'widget',

    /**
     * Allow undo actions.
     */
    canUndo: true,

    /**
     * Init the plugin.
     */
    init: function (editor) {
      this.registerWidget(editor);
      this.addCommand(editor);
      this.addIcon(editor);
    },

    /**
     * Add the command to the editor.
     */
    addCommand: function (editor) {
      var self = this;

      editor.addCommand('d500px_add_photo', {
        exec: function (editor, data) {
          // If the selected element while we click the button is an instance
          // of the d500px_add_photo widget, extract it's values so they can be
          // sent to the server to prime the configuration form.
          var existingValues = {};
          if (editor.widgets.focused && editor.widgets.focused.name == 'd500px_add_photo') {
            existingValues = editor.widgets.focused.data.json;
          }
          Drupal.ckeditor.openDialog(editor, Drupal.url('d500px-wysiwyg/dialog/' + editor.config.drupal.format), existingValues, {
            title: Drupal.t('Add 500px Photo'),
            dialogClass: 'd500px-wysiwyg-dialog'
          });
        }
      });
    },

    /**
     * Register the widget.
     */
    registerWidget: function (editor) {
      var self = this;
      editor.widgets.add('d500px_add_photo', {
        mask: true
      });
    },

    /**
     * Add the icon to the toolbar.
     */
    addIcon: function (editor) {
      if (!editor.ui.addButton) {
        return;
      }
      editor.ui.addButton('d500px_add_photo', {
        label: Drupal.t('Add 500px Photo'),
        command: 'd500px_add_photo',
        icon: this.path + '/icon.png'
      });
    }
  });


})(jQuery);
