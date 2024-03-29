import { Command } from 'ckeditor5/src/core';

export default class InsertLinkPluginCommand extends Command {
  execute(title, link, textColor, bgColor) {
    const { model } = this.editor;

    model.change((writer) => {
      const linkPlugin = writer.createElement('linkPlugin');
      const linkContent = writer.createElement('linkContent');

      writer.append(title, linkContent);
      writer.setAttribute('href', link, linkPlugin);
      writer.setAttribute('style', `color: ${textColor}; background-color: ${bgColor};`, linkContent);
      writer.append(linkContent, linkPlugin);
      model.insertContent(linkPlugin);
    });
  }

  refresh() {
    const { model } = this.editor;
    const { selection } = model.document;

    const allowedIn = model.schema.findAllowedParent(
      selection.getFirstPosition(),
      'linkPlugin',
    );

    this.isEnabled = allowedIn !== null;
  }
}

