# LocalGovDrupal Alert banner

LocalGovDrupal Alert banner module, adds a global alert banner block and entity.

## Order of alerts

In terms of order, it should be Notable Death -> Major -> Minor -> Announcement and then in date updated order.

## Scheduling alert banners

Scheduling the publishing and unpublishing of alert banners is done using the [Scheduled Transitions](https://www.drupal.org/project/scheduled_transitions) module. Scheduling is not enabled by default. To turn it on just enable the Scheduled Transitions module.

## Disable provided CSS.

This module will provide default CSS colours so it can be used out the box. If you wish to theme these yourself, you can disable the provided CSS with the following code in your themes `THEMENAME.info.yml` file:
```yaml
libraries-override:
  localgov_alert_banner/alert_banner:
    css:
      component:
        css/localgov-alert-banner.css: false
```

## Maintainers

 Current maintainers: 

  - AndyB https://github.com/andybroomfield
