# test-drupal11 for DDEV testing only:

- Clone this repo, which was created with the Drupal 11 quickstart
- To update the repo: `ddev composer update --with-all-dependencies`
- `git add -u` and `git add .`
- `ddev config --auto`
- `ddev drush si -y demo_umami --account-pass=admin`
- `ddev export-db --file=.tarballs/db.sql --gzip=false`
- `tar -czf .tarballs/db.sql.tar.gz -C .tarballs db.sql`
- Run `git push`, create a new release adding `.tarballs/db.sql.tar.gz` as an asset
- Update the URLs in `ddev/ddev` ddevapp_test.go for the new release
- Rerun the tests for Drupal11 with `GOTEST_SHORT=18 make testpkg TESTARGS="-run TestDdevFullSiteSetup"`

