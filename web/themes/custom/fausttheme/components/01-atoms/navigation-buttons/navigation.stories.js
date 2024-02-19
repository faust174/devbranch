import button from './navigation.twig';

import buttonData from './navigation.yml';
/**
 * Storybook Definition.
 */
export default { title: 'Atoms/Navigation' };

export const twig = () => button(buttonData);

// export const twigAlt = () => button(buttonAltData);
