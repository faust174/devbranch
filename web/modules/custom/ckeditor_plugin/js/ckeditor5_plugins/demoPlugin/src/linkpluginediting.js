import { Plugin } from 'ckeditor5/src/core';
import InsertLinkPluginCommand from './insertlinkplugincommand';

export default class LinkPluginEditing extends Plugin {
  init() {
    this._defineSchema();
    this._defineConverters();
    this.editor.commands.add(
      'insertLinkPlugin',
      new InsertLinkPluginCommand(this.editor),
    );
  }

  _defineSchema() {
    const schema = this.editor.model.schema;

    schema.register('linkPlugin', {
      isObject: true,
      allowWhere: '$block',
      allowAttributes: ['href'],
    });

    schema.register('linkContent', {
      isLimit: true,
      allowIn: 'linkPlugin',
      allowContentOf: '$block',
      allowAttributes: ['color', 'bgColor', 'style'],
    });
  }

  _defineConverters() {
    const { conversion } = this.editor;

    conversion.for('upcast').attributeToAttribute({
      model: 'linkPlugin',
      view: {
        name: 'a',
        classes: 'link-plugin',
      },
    });

    conversion.for('upcast').elementToElement({
      model: 'linkContent',
      view: {
        name: 'span',
        classes: 'link-content',
      },
    });

    conversion.for('downcast').elementToElement({
      model: 'linkPlugin',
      view: (modelElement, { writer }) => {
        const container = writer.createEditableElement('a', {
          href: modelElement.getAttribute('href'),
          class: 'link-plugin',
        });
        return container;
      },
    });

    conversion.for('downcast').elementToElement({
      model: 'linkContent',
      view: (modelElement, { writer }) => {
        const span = writer.createEditableElement('span', {
          class: 'link-content',
          style: modelElement.getAttribute('style'),
        });
        return span;
      },
    });
  }
}
