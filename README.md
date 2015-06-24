# CAVEAT

This is a forked version of [Bosnadev/Repositories](https://github.com/Bosnadev/Repositories) for personal use. This Laravel 5 package can be used to decouple your code from the storage layer.

### Installation

Add following to  your `composer.json` and run `composer update` (Note that this package is not submitted to packagist.org):
```json
"repositories": [
  {
    "type": "vcs",
    "url": "git@github.com:appkr/repository.git"
  }
],
"require": {
  "...",
  "appkr/repository": "dev-master"
},
```

### Usage

Usage is basically the same as the original package. But prototype of some public methods may differ from the original. Check out [`RepositoryInterface.php`](https://github.com/appkr/repository/blob/master/src/Contracts/RepositoryInterface.php) and [`Condition.php`](https://github.com/appkr/repository/blob/master/src/Conditions/Condition.php).

### Changes made from the original package

- PSR-2 coding convention applied.
- Name changed from `Criteria` to `Conditions`.
- `CriteriaInterface` removed.
- New `setEagerLoads() or with()` method can be chained with `all()`, `paginate()`, `find()`, `findBy()`, `fineAllBy()`, `findWhere()`.
- `setOrder() or orderBy()` added.
- In replace for `pushCriteria()`, `getByCriteria()`, new methods `setConditions()` was introduced for filtering the query result.
- The repository is now firing a couple of events like `repository.updating`, ...

### Example usage

Fetch collection of 5 films with eager load and pagination as descending order:

```php
$this->film->with('author')->orderBy('created_at', 'desc')->paginate(5);
```

### Conditions

Here is a simple condition:

```php
<?php 

namespace App\Repositories\Conditions;

use Appkr\Repository\Contracts\RepositoryInterface as Repository;
use Appkr\Repository\Conditions\Condition;

class Like extends Condition
{
    public $column;

    public $search;

    public function __construct($column, $search)
    {
        $this->column = $column;
        $this->search = $search;
    }

    /**
     * @param            $model
     * @param Repository $repository
     *
     * @return mixed
     */
    public function apply($model, Repository $repository)
    {
        return $model->where($this->column, 'like', '%' . $this->search . '%');
    }
}
```

Now, inside you controller class you call `setConditions()` method:

```php
<?php 

namespace App\Http\Controllers;

use App\Repositories\FilmsRepository as Film;

class FilmsController extends Controller {

    private $film;

    public function __construct(Film $film) {
        $this->film = $film;
    }

    public function index() {
        $payload = $this->film->setConditions([
            new Like('title', 'Avenge'),
            function($model) {
                return $model->orWhere('published', '>=', '2010');
            }
        ])->all();
        
        return \Response::json($payload);
    }
}
```

### LICENSE

Since the owner of the original package does not post a license, I don't know I'm allowed to modify his work and redistribute it. Anyway it is still under his package as a branch, this branch also follows his license policy.
