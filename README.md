## Spark
### About
Complete contest panel. Features include team registration, pizza orders, compilation, judging, clarifications, and appeals.

Written by Andy Sturzu with contributions by Jonathan Zong.
### Stuff to Edit to get contest working
* server/server.js (mysql details, md5(admin key))
* judge.php (mysql details)
* config.php (mysql details, contest info, md5(admin key))
* schools.json
* problems.json (needs to follow folder and example format)

### To run a contest
Import the database structure ``` thscs.sql ``` using any MySQL administration tool or through the command line.

Use ONLY the shell script to start the servers. ``` sh run.sh ```

Also, be sure to run ``` npm install ``` in ``` /server ```.

Log into the admin panel by visiting ``` /admin ``` using ``` admin.key ``` make sure to generate a new copy every time you run a contest.

Press the start button when you're ready.

In case you encounter an error that leads to a crash, you must increment negative time.

This code has only been tested on OS X with MAMP (>= PHP 5.4).

Any other operating system will require further configuration.
