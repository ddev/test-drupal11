# test-drupal11 for DDEV testing only:

- Clone this repo, which was created with the Drupal 11 quickstart
- `ddev config --auto`
- To update the repo: `ddev composer update --with-all-dependencies && ddev composer require drush/drush`
- `ddev config --update`
- `git add -u` and `git add *`
- `ddev drush si -y demo_umami --account-pass=admin`
- Log in and edit the content "Super easy vegetarian pasta bake" (https://test-drupal11.ddev.site/en/node/3/edit) to change the "recipe name" to "Super easy vegetarian pasta bake TEST PROJECT".
- `ddev export-db --file=.tarballs/db.sql --gzip=false`
- `tar -czf .tarballs/db.sql.tar.gz -C .tarballs db.sql`
- `cp .tarballs/Logo.png web/sites/default/files/`
- `tar -czf .tarballs/files.tgz -C web/sites/default/files/ .`
- Run `git push`, create a new release adding `.tarballs/db.sql.tar.gz` and `.tarballs/files.tgz` as assets
- Update the URLs in `ddev/ddev` ddevapp_test.go for the new release
- Rerun the tests for Drupal11 with `GOTEST_SHORT=19 make testpkg TESTARGS="-run TestDdevFullSiteSetup"`
