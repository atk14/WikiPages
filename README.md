Wiki Pages
----------

A set of features that allow to maintain the knowledge database (wiki) in administration.

Wiki Pages is designed for applications built on Atk14Skelet.

Prerequisites
=============

### User Authorization

Installation
============

    cd path/to/your/project/
    composer require atk14/wiki-pages

    ln -s ../../../vendor/atk14/wiki-pages/src/app/forms/admin/wiki_pages app/forms/admin/
    ln -s ../../../vendor/atk14/wiki-pages/src/app/forms/admin/wiki_attachments app/forms/admin/
    ln -s ../../../vendor/atk14/wiki-pages/src/app/views/admin/wiki_pages app/views/admin/
    ln -s ../../../vendor/atk14/wiki-pages/src/app/views/admin/wiki_attachments app/views/admin/
    ln -s ../../vendor/atk14/wiki-pages/src/app/models/wiki_page.php app/models/
    ln -s ../../vendor/atk14/wiki-pages/src/app/models/wiki_attachment.php app/models/
    ln -s ../../vendor/atk14/wiki-pages/src/app/helpers/modifier.wiki_markdown.php app/helpers/
    ln -s ../../vendor/atk14/wiki-pages/src/app/helpers/block.wiki_markdown.php app/helpers/
    ln -s ../../../vendor/atk14/wiki-pages/src/app/controllers/admin/wiki_pages_controller.php app/controllers/admin/
    ln -s ../../../vendor/atk14/wiki-pages/src/app/controllers/admin/wiki_attachments_controller.php app/controllers/admin/
    ln -s ../../vendor/atk14/wiki-pages/src/test/models/tc_wiki_attachment.php test/models/
    ln -s ../../vendor/atk14/wiki-pages/src/test/models/tc_wiki_page.php test/models/
    ln -s ../../vendor/atk14/wiki-pages/src/config/routers/wiki_pages_router.php config/routers/



Copy migration to a proper filename into your project:

    cp vendor/atk14/wiki-pages/src/db/migrations/0150_wiki_pages.sql db/migrations/

Linking a proper style form either for Bootstrap 4 (scss) or Bootstrap 3 (less).

    ln -s ../../../vendor/atk14/wiki-pages/src/public/admin/styles/_wiki_pages.scss public/admin/styles/

    # or for Bootstrap 3

    ln -s ../../../vendor/atk14/wiki-pages/src/public/admin/styles/wiki_pages.less public/admin/styles/


Now include the selected style to your application style.

Load WikiRouter:

    <?php
    // file: config/routers/load.php

    // ...

    Atk14Url::AddRouter("AdminRouter");
    Atk14Url::AddRouter("admin","WikiPagesRouter");

    // ...

Add a link to Wiki Pages to the administration:

    <?php
    // file: app/controllers/admin/admin.php

    // ..

    array(_("404 Redirections"),    "error_redirections"),
    array(_("Wiki"),          "wiki_pages,wiki_attachments"),

    // ..

[//]: # ( vim: set ts=2 et: )
