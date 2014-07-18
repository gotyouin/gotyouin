; drush make --no-core -y --contrib-destination=. drupanium_plugin.make

core = 7.x
api = 2

; Regular modules, nothing special here
projects[features][subdir] = "drupanium"
projects[ctools][subdir] = "drupanium"
projects[views][subdir] = "drupanium"

projects[strongarm][subdir] = "drupanium"

projects[services_search][type] = module
projects[services_search][subdir] = drupanium
projects[services_search][download][type] = "git"
projects[services_search][download][url] = "http://git.drupal.org/project/services_search.git"

projects[services_views][subdir] = "drupanium"
projects[services_views][version] = 1.x-dev

projects[services][type] = module
projects[services][subdir] = drupanium
projects[services][download][type] = "git"
projects[services][download][url] = "http://git.drupal.org/project/services.git"
projects[services][download][revision] = 8a848e46059ff78312f972ace92c4bf4c8b68fa8
projects[services][download][branch] = master

projects[flag][subdir] = "contrib"
projects[flag][version] = 2.0-beta6

projects[flag_service][subdir] = "contrib"
projects[flag_service][subdir] = 3.x-dev

projects[libraries][subdir] = "contrib"
projects[libraries][version] = 1.0


; Get the spyc library required by the rest server
libraries[spyc][download][type] = "file"
libraries[spyc][download][url] = "http://spyc.googlecode.com/files/spyc-0.5.zip"
libraries[spyc][download][md5] = "4caa361e868e2a61e70e0fc901b6b057"
