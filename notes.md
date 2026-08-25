
## Module 3:

### 21_8_26 Retrospective

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

### 22_8_26 Retrospective

- module 3's Data layer is done <- what does that mean.
  - The parts of the app that represent & store data (as opposed to the parts that serve it over HTTP) are ready.
  - In this project that was:
    - the entity (`User` - the PHP class describing a user)
    - the migration (the versioned instruction that created the real `user` table)
    - the live table in MYSQL
  - Together they mean your app can hold user data. There is no way to reach that data right now though.
- The `user` table exists, but it's empty. We have no way to write or read users yet.
- So next step is to build those end points. This is the controller + routes layer.
- This means turning your database into HTTP endpoints like `GET /users` and `POST /users` etc.
- Endpoints need:
  - controller class
    - method inside it
      - #[Route] attribute inside that saying which URL + HTTP verb trigger it.

`docker compose exec php php bin/console make:controller`
asks name: `USerController`
asks 'want tests?': I said 'yes', but that caused it to raise an exception because it's in an `[experimental]` phase and needed PHPUnit installed (whatever that is) - skip for now.

- symfony creates:
  -  `src/Controller/USerController.php`
  -  + a matching template file (`.twig`) <- ignore, you're building a JSON API, not rendering HTML.

- check out the #[Route] - that is how you're going to define endpoints.
- final file:

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(UserRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }
}
```

- in line 12 `#[Route('/user', name: 'app_user')]`
  - `/user` is the URL path
  - `name: 'app_user'` is the internal route name. It's a label for Symfony to refer to this route from inside your own code. Like a variable, but it must be unique through the whole app.
 
#### Repositories 

- the controller can't talk to the DB directly, it goes through this.
- It was created when you made the entity.
- It has a method `findAll()`
- In modern Symfony the method above accesses the repository through dependency injection:
  - `public function index(UserRepository $repo): JsonResponse`
 
- repository is the fusty archivist that allows you to read, the entity manager is the liberal manager who let's you deposit and edit items in the museum.

#### 2 Errors

1. I forgot to ad a use statement `use App\Repository\UserRepository;`
2. This code is running in the PHP container, so the `127.0.0.1` i set in the `.env` is now wrong. The database service is called `mysql`
  - `DATABASE_URL=mysql://root:password@mysql:3306/app`

- At this point the entire stack is working correctly end-to-end. Browser -> nginx -> PHP controller -> repository -> database -> JSON reposponse

### Docker metaphor -> The Magical University

Docker is a company run by Faeries. They let you rent out pop-up tents in which you can set up any kind of work process you need. They have custom tents, like kitchens, or workshops, or you can customise your own. In these tents there are Faeries doing the work. Humans are not allowed in because the air is poisonous to humans. The tents have an inner and an outer wall. In order to pass things in and out there are hatches and tubes that connect them. Two different outer hatches can lead to the same inner hatch. Outer hatches have 4 digit numbers (8080), inner hatches have 2 digit numbers (80), so a tube might be referred to as 8080:80. The Faeries themselves exist on 2 plains of reality. The Fairie dimention is a vast ocean with small islands. Each island represents a Docker project. On such an island the physical world disappears. This means that Faeries can wander out to other tents on the island, right up to the inner wall hatch and pass in and retrieve what they need to. Then they wander back and shimmer into the human realm if they need to do something there.

## 24_8_26

### entity manager -> create

```src/Controller/UserController.php 
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = $request->toArray();
    }
```

- red squiggly-lines side-quest. Docker wasn't running - then my mac couldn't see the `vendor/` folder which the container can. So the app runs via Docker, but VSCode reads it as 'class not found'. The Symfony and Doctrine classes are installed inside the PHP container (or a path the Mac's PHP tooling is pointed at), but the editor can't see it do it says "this class doesn't exst", even though the code runs just fine. (In the magical university, this is an administrator standing outside the tents, with a clipboard, panicing because he can't see the things his clipboard says muct be here, ebcause they're in the Faerie tent labelled 'PHP', which is inaccessible to humans.

- when creating the `user` object you create it without any data, then use the `set` methods like this:

```src/Controller/UserController.php
        $data = $request->toArray();
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setCreatedAt(new \DateTimeImmutable()); <= this property is non-nullable so has to be set. The DatTimeImmutable without arguments defaults to now.
```

- At this point the `user` object exists in memory, but not in the database , so we need to save it with the entity manager `$em`.

```
$em->persist($user) <= "start tracking this object, i intend to save it"
$em->flush(); <= "now actually write everything to the database" This is the moment Doctrine talks to the DB. You can persist several objects and then flush() them all together
```

- The `persist` & `flush` work flow mirrors git commit & push exactly.
- and it should return a JSON response object to fufill the typing hint. SO full method:

```
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = $request->toArray();
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setCreatedAt(new \DateTimeImmutable());

        return $this->json($user);
    }
```

- Then tried testing this with a `POST` method in Postman, but because the properties are created private, with public getter methods. But I have to install the Serializer to make those public getters accessible.
  - Installing a brand new package is a job for the package manager - the one that pulls code down and adds it to `vendor/`
  - Composer with `require`
  - `docker compose exec php composer require symfony/serializer-pack`
  - (hammer home that compose and composer are not the same)
  - it didn;t work because: `OCI runtime exec failed: exec failed: unable to start container process: exec: "composer": executable file not found in $PATH: unknown`
  - this indicates that `composer` doesn't exist within the container, so I should run it on my machine, and it will be mirrored in the container:
  - `composer require symfony/serializer-pack`


```UserController.php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user', methods: ['GET'])]
    public function index(UserRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/user', name: 'app_user_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = $request->toArray();
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        return $this->json($user);
    }
}
```

### DELETE


- bug: there was an invisible, zero-width character at the end of my ROUTE file blowing up Postman. Probably introduced by a copy/paste

### Namespacing rule

- Take the file's path under `src`.
- Swap `src` for `App`.
- use `\` instead of `/`
- The namespace is the folder path
- The class name = the file name without .php
- For example:
  - src/Service/UserService.php -> App\Service\UserService
