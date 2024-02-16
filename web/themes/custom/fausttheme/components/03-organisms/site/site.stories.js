import footerTwig from './site-footer/site-footer.twig';
import siteHeader from './site-header/header.twig';
// import siteHeader from './site-header/site-header.twig';

import footerSocial from '../../02-molecules/menus/social-menu/social-menu.yml';
import footerMenu from '../../02-molecules/menus/inline/inline-menu.yml';
import breadcrumbData from '../../02-molecules/menus/breadcrumbs/breadcrumbs.yml';
import mainMenuData from '../../02-molecules/menus/main-menu/main-menu.yml';

import leftHeaderMenuData from '../../02-molecules/menus/left-header-menu/left-header-menu.yml';
import rightHeaderMenuData from '../../02-molecules/menus/right-header-menu/right-header-menu.yml';
import siteLogoData from '../../01-atoms/text/text/logo.yml';

import '../../02-molecules/menus/main-menu/main-menu';
// import '../../02-molecules/menus/right-header-menu/right-header-menu.yml';
// import '../../02-molecules/menus/left-header-menu/left-header-menu.yml';

/**
 * Storybook Definition.
 */
export default {
  title: 'Organisms/Site',
  parameters: {
    layout: 'fullscreen',
  },
};

export const footer = () => footerTwig({ ...footerSocial, ...footerMenu });

export const header = () =>
  siteHeader({
    ...breadcrumbData,
    ...mainMenuData,
    ...leftHeaderMenuData,
    ...rightHeaderMenuData,
    ...siteLogoData,
  });
