Inventory - server side app
===
This is an php-server for inventory android app.
Server allows to view and edit data by QR-code (client-side OCR) and view lists of items.
This server is intended for local use and uses no authentication.

=Files=

- sqlcon.php
    Contains wrapper class for mysqli. 
    It provides mySQL connection and running queries.
    Result of query may be returned as XML, JSON or raw.
    
- index.php
    Main server unit. Contains app buisness logic.
    
- errors.php
    Contains error-code constants.

- mulvar.php
    global variables initialization code.

- states.php
   Contains states constants (for db items).
    
- sql.sql
    Contains SQL query to create data base.
    
- README
    This file.
