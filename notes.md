
## Module 3:

### 21_8_26

- I created the User entity. That is a PHP class which maps to a database table. Each property on the class becomes a column in the table. I used Symfony’s interactive generator to make it with the following command:

`Docker compose exec php php bin/console make:entity`

- That command asks a bunch of questions to determine the input types and their optionality.

- There was an error because `make:entity` wasn’t there - I had to use composer, the symphony package manager to install the maker bundle as a dev dependency:

`composer require symphony/maker-bundle —dev`

I accidentally hit enter too soon, but it turns out if you run `Docker compose exec php php bin/console make:entity` a second time with the same Class name, it puts you in update mode, so no panic.

Migration -> creating the tables (not the databases):

- The following command creates the migration file, but it does not migrate:
`Docker compose exec php php bin/console make:migration`

Then to run the migration:

`php bin/console make migration`

Then it couldn’t find the database because I was running the command on my MacBook, but the database is running on docker, which is hard to orient oneself with. I had set the name of the database as `mysql`, which is the Docker service name (whatever that means) for the compose file. But running it from my computer I had to swap that for 127.0.0.1 to show it was hosted locally. And the port was 3306 - the reserved port for mysql.

Was: `DATABASE_URL=mysql://root:password@mysql:3306/app`
Changed to: `DATABASE_URL=mysql://root:password@127.0.0.1:3306/app`

Then there was a brief rabbit-hole about what it means to run a command within Docker or without. And how one can know if one is in Docker or not.

Then a discussion about how a migration makes Tables, but doesn’t make databases. Drawers not filing cabinets.

We looked at the migration file created. It has a class with 2 public methods -> up and down. We looked at how that allowed migrations to be run and rolled back. So every change is reversible. Each migration creates a file like this. They’re given auto-generated timestamp filenames so Symfony can keep track of their order, which is super important because later migrations only work on previous migrations.

Looked at the ‘getDescription’ method in each of these migration classes.

Ran the migration - double checked with: `php bin/console dal:run-sql “SHOW TABLES”`

Worked but printed nothing, so: `php bin/console dal:run-sql “SHOW TABLES” — force-fetch`
