import LinkPluginEditing from './linkpluginediting';
import LinkPluginUI from './linkpluginui';
import { Plugin } from 'ckeditor5/src/core';

export default class LinkPlugin extends Plugin {
  static get requires() {
    return [LinkPluginEditing, LinkPluginUI];
  }
}
