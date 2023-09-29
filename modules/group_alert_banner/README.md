# Group Alert banner
LocalGov Alert banner integration with the [Group module](https://www.drupal.org/project/group).

## Configuration
### User permissions
If you are using this module alongside the [localgov_microsites_group](https://github.com/localgovdrupal/localgov_microsites_group) module, then user permissions will be automatically setup during module installation.

Otherwise enable at least the following permissions for **Groups**:
- "Entity: View any alert banner entities" for both anonymous and authenticated users.
- "Access Alert banner listing page" for group admins.

### Block placement
If you are using the [localgov_microsites_base theme](https://github.com/localgovdrupal/localgov_microsites_base), which is the default theme of the [LocalGov Drupal Microsites distribution](https://github.com/localgovdrupal/localgov_microsites_project), an Alert banner block will be automatically placed in the *Header* region.  For all other themes, place an Alert banner block in a theme region towards the top of the page.

## Banner listing
Once this module is installed, an *Alert banner* tab should appear in each Group page.  This page will list all the banners belonging to a Group.
