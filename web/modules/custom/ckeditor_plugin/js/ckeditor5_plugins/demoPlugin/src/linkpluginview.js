export default class LinkPluginView {
  constructor(editor, defaultColor) {
    this.editor = editor;
    this.defaultColor = defaultColor;
  }

  openModal() {
    const existingModal = document.querySelector('.modal');

    if (existingModal) {
      existingModal.style.display = (existingModal.style.display === 'block') ? 'none' : 'block';
      return;
    }

    const modal = document.createElement('div');
    modal.classList.add('modal');

    const modalContent = document.createElement('div');
    modalContent.classList.add('modal-content');
    modalContent.style.display = 'flex';
    modalContent.style.flexDirection = 'column';
    modalContent.style.position = 'relative';

    const closeBtn = document.createElement('span');
    closeBtn.classList.add('close');
    closeBtn.textContent = '×';
    closeBtn.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    const titleInput = document.createElement('input');
    titleInput.setAttribute('type', 'text');
    titleInput.setAttribute('placeholder', 'Enter title');
    titleInput.id = 'linkTitle';

    const linkInput = document.createElement('input');
    linkInput.setAttribute('type', 'text');
    linkInput.setAttribute('placeholder', 'Enter URL');
    linkInput.id = 'linkURL';

    const textColorInput = document.createElement('input');
    textColorInput.setAttribute('type', 'color');
    textColorInput.id = 'textColor';
    textColorInput.setAttribute('placeholder', '#000');
    textColorInput.style.height = '15px';

    const bgColorInput = document.createElement('input');
    bgColorInput.setAttribute('type', 'color');
    bgColorInput.id = 'bgColor';
    bgColorInput.setAttribute('value', this.defaultColor);
    bgColorInput.style.height = '15px';

    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Save';
    saveBtn.addEventListener('click', (event) => {
      event.preventDefault();
      const title = document.getElementById('linkTitle').value;
      const link = document.getElementById('linkURL').value;
      const textColor = document.getElementById('textColor').value;
      const bgColor = document.getElementById('bgColor').value;

      this.insertLink(title, link, textColor, bgColor);

      titleInput.value = '';
      linkInput.value = '';
      textColorInput.value = '#000000';
      bgColorInput.value = '#FFFFFF';

      modal.style.display = 'none';
    });

    modalContent.appendChild(closeBtn);
    modalContent.appendChild(titleInput);
    modalContent.appendChild(linkInput);
    modalContent.appendChild(textColorInput);
    modalContent.appendChild(bgColorInput);
    modalContent.appendChild(saveBtn);

    modal.appendChild(modalContent);

    const toolbar = document.querySelector('.ck-editor__top');
    if (toolbar) {
      toolbar.appendChild(modal);
    }

    modal.style.display = 'block';
  }

  insertLink(title, link, textColor, bgColor) {
    const command = this.editor.commands.get('insertLinkPlugin');
    const correctedLink = link.startsWith('http') ? link : `http://${link}`;
    command.execute(title, correctedLink, textColor, bgColor);
  }
}


