# Next Notes (v0.9.1-beta)
[![Build Status](https://travis-ci.org/janis91/nextnotes.svg?branch=master)](https://travis-ci.org/janis91/nextnotes) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/janis91/nextnotes/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/janis91/nextnotes/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/janis91/nextnotes/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/janis91/nextnotes/?branch=master) [![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](http://www.gnu.org/licenses/agpl-3.0)

**This software is in beta phase and should not be integrated in any production environment. If you tested it and want to give feedback, please open an issue. Thank You!**.
A new Nextcloud app for enhanced organization of notes.
The Next Notes app gives you much more possibilities for your personal organization. With Next Notes you will be able to create notes with a simple markup for enhanced usability, search for your notes by tags or in fulltext. And a future version will give you the opportunity to share your notes with others or let them even edit them completely without the constraint of being logged in.

See [Website](http://janis91.github.io/nextnotes/) for screenshots and more about the usage and examples or use cases.
Try it!

## Installation
Install the app from the [Nextcloud AppStore](http://apps.nextcloud.com) or download/clone the git [release](https://github.com/janis91/nextnotes/releases) and place the content in **owncloud/apps/** or **nextcloud/apps/**.

## Contribute
If you recognize any bugs or if you want to propose new features or enhancements, please open an issue here on github or submit your pull-request. Everyone is welcome to contribute.

## Prerequisites, Requirements and Dependencies
* **[Nextcloud 10](https://nextcloud.com/)** or higher
* A fork of **[SimpleMDE](https://github.com/NextStepWebs/simplemde-markdown-editor)** is used for the beautiful md editor surface.
* A fork of **[Markdown CSS](https://github.com/sindresorhus/github-markdown-css)** is used for the styling.

## ROADMAP
Ideas for future improvements:

### Version 1.0:
* L10N

### Version 2.0:
* Separate title and content from each other. (Has effect on searchqueries and some other things like content view container and creation/update.)
* Share option should share the tags also -> no user specific call for tags (maybe it is better to create a custom table with shared state and integrate in existing service)
* Shared date could be a sorting criteria
* Share option with edit and with view only option. Also there should be an open page with whole navigation and so on for shared "public" content. The shared "private" (link-only) notes should stay hided here
* Duplicate a note (button for duplicate) -> when shared, make a local copy.


##Legal Information
###Disclaimer
The software is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.

###Author
Janis Koehr

###License
[GNU AFFERO GENERAL PUBLIC LICENSE](https://github.com/janis91/nextnotes/blob/master/COPYING)
