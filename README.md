Welcome to Feegra, the Facebook Feed Grabber.

Feegra scraps Facebook pages for posts, and stores them in an SQLite database.
The pages are queued and handled sequentially until all posts from all pages are
retrieved.


USAGE
-----

1. First configure Feegra, configurations are stored in `config.php`. You need
   to add your Facebook App Id, and App Secret, and a User Access Token. See
   Limitations section bellow. You can also, optionally, change paths for the
   database and logfile, and change the pagination size.

2. Then initialize Feegra by running:
`feegra init`

This prepares the Database and sets up a cronjob to `process` the queue once
every minute.

3. To add a page to the queue, run:
`feegra add {facebook_page_id}`

Where `{facebook_page_id}` is replaced by the ID of the page you want to scrap.
The id can be easily retrieved from the page URL, it usually looks like:

`https://facebook.com/page-id/possible-other-stuff`

4. To process one step of the queue (one page)

5. To list queued pages, run
`feegra list`

6. To list retrieved posts of one page, run:
`feegra list {facebook_page_id}`


LIMITATIONS
-----------

Currently Facebook does not allow access to the public feeds of Facebook pages.
For apps without submitting the app to Facebook staff for review, and getting
subsequent approval. Which is currently out of the project's scope.

Developers can only access the feeds of pages if they have the role of an admin
on that page. Or through Facebook Test Users on Test Pages.

The application is currently only tested with a Test User user-token to scrap
Test Pages.


See:
- See the 'Permissions' section under:
  https://developers.facebook.com/docs/graph-api/reference/v3.2/page/feed#read

- See the "Page Public Content Access" section under:
  https://developers.facebook.com/docs/apps/review/feature/#reference-PAGES_ACCESS
  
- For instructions on how to create test users:
  https://developers.facebook.com/docs/apps/test-users/

- For instructions on how to create test pages:
  https://developers.facebook.com/docs/apps/test-pages/ 
 

TODO
----

* Refactor the logging system, and add more logs.
* Refactor all the random exit() into exceptions, and use a central exit point.
* Allow re-queuing pages to get new page and post updates after the scrapping
  completes.
* Make crontab entry optional, users can just call `process` from their own
  scripts.
* Maybe integrate Facebook user authentication & authorization to allow users to
  use their own tokens on public pages.
