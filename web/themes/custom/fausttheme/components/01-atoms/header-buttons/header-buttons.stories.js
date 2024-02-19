import button from './header-button.twig';

import buttonData from './header-button.yml';
import buttonAltData from './header-button-alt.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Atoms/Header-button' };

export const twig = () => button(buttonData);

export const twigAlt = () => button(buttonAltData);
