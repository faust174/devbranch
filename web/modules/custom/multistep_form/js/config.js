(function (Drupal, once, drupalSettings) {
  'use strict';

  Drupal.behaviors.multistepForm = {
    attach: function (context) {
      once('multistepForm', '#wrapper', context).forEach(function (element) {
        function updateWeightOptions(context) {
          let checkboxes = context.querySelectorAll('.multistep-checkbox');
          let disabledSteps = Array.from(checkboxes).filter(checkbox => checkbox.checked).length;

          let selects = context.querySelectorAll('.weight-select');
          selects.forEach(select => {
            let stepOptions = parseInt(select.dataset.options);
            let newOptions = 3 - disabledSteps;
            if (newOptions < 1) {
              newOptions = 1;
            }

            let currentValue = parseInt(select.value);

            select.innerHTML = '';

            for (let i = 1; i <= newOptions; i++) {
              let option = document.createElement('option');
              option.value = i;
              option.text = i;
              console.log(option);
              select.appendChild(option);
            }

            if (!isNaN(currentValue) && currentValue >= 1 && currentValue <= newOptions) {
              select.value = currentValue;
            }
          });
        }

        updateWeightOptions(context);

      context.querySelectorAll('.multistep-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
          let isChecked = this.checked;
          let step = this.dataset.step;
          context.querySelector('.weight-select[data-step="' + step + '"]').disabled = isChecked;
          localStorage.setItem('multistep_checkbox_' + step, isChecked ? 'checked' : '');
          updateWeightOptions(context);
        });
      });

      context.querySelectorAll('.multistep-checkbox').forEach(checkbox => {
        let step = checkbox.dataset.step;
        let isChecked = localStorage.getItem('multistep_checkbox_' + step);
        if (isChecked === 'checked') {
          checkbox.checked = true;
          checkbox.dispatchEvent(new Event('change'));
        }
      });
      updateWeightOptions(context);
    });
    }
  };
})(Drupal, once, drupalSettings);
