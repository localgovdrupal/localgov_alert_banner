# Group Alert banner
LocalGov Alert banner integration with the [Group module](https://www.drupal.org/project/group).

## Configuration
If you are using this module alongside the [localgov_microsites_group](https://github.com/localgovdrupal/localgov_microsites_group) module, then user permissions will be automatically setup during module installation.

Otherwise enable at least the following permissions for **Groups**:
- "Entity: View any alert banner entities" for both anonymous and authenticated users.
- "Access Alert banner listing page" for group admins.

## Banner listing
Once this module is installed, an *Alert banner* tab should appear in each Group page.  This page will list all the banners belonging to a Group.
