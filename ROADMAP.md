#Ideas for future improvements:

##Version 1.0:
* Create unit tests and integration tests.
* Test the javascript. jasmine configuration and npm with package.json
* Logging of the app would be great. => Util::writelog() and logException()
* Delete user hook is necessary
* L10N
* Test with mysql and sqlite => especially the search component
* Build config with travis.yml
* Document installation and usage in README, INFO.xml and CHANGELOG. Update Version number. And recheck the .github files (links etc.)

##Version 2.0:
* Separate title and content from each other. (Has effect on searchqueries and some other things like content view container and creation/update.)
* Share option should share the tags also -> no user specific call for tags (maybe it is better to create a custom table with shared state and integrate in existing service)
* Shared date could be a sorting criteria
* Share option with edit and with view only option. Also there should be an open page with whole navigation and so on for shared "public" content. The shared "private" (link-only) notes should stay hided here
