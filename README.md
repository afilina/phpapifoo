# PHP API Foo

PHP >=5.6

Use this package to easily implement RESTful APIs in any framework. Currently, only CakePHP 3.2 is supported, but I'll be adding adapters for other frameworks soon.

You can install this file via [Composer](https://getcomposer.org/doc/00-intro.md):
```
composer require afilina/phpapifoo dev-master
```

## How is this different from framework-specific plugins?

The idea is for you to be able to use your framework of choice. This package doesn't do too much magic, meaning that it's as simple as instantiating a few classes to offload some of the boilerplate, but without creating complicated abstraction layers. This approach is meant to lower the barrier to entry. It also does a good job at keeping all your application logic right where you would expect it, incontrollers and repositories, as opposed to configuration files and listeners.

The examples will speak for themselves.

## How is this different from code generators?

The idea is to make it simple to drop the functionality into any codebase. This can be particularly useful with legacy. You also don't have to learn any Domain Specific Languages. You only need to know PHP and your favorite framework. However, I believe that there could be value in generating REST API code while leveraging this library as a gateway to all the frameworks.

## Usage

The first step is to wrap the request object in the framework's adapter, such as CakeRequest, then in an ApiRequest. The ApiRequest will extract pagination, sorting and filter information. This is what you would typically do in your controller:

```
$apiRequest = new ApiRequest(new CakeRequest($this->request));
```

The `CakeRequest` wrapper will no longer be required once frameworks implement their request objects with PSR-7.

The second step is to wrap the repository/table in the framework's adapter, such as CakeOrm, then in an ApiRepository. The ApiRepository will add parts to the query builder based on the ApiRequest object provided. This is what you would typically do in your repository/table:
```
$apiRepository = new ApiRepository(new CakeOrm($this));
$results = $apiRepository->getList($query, $apiRequest);
```

You have full control over the query before you pass it to `getList`. You can pick your columns, joins, hydration, etc. You also retain full control over the routing.

The `getList` method will go through the request object and find criteria to apply to your query. For example, if it finds title=value in the GET parameters, it will attempt to call addTitleFilter on the repository/table, which is why you passed `$this` to the contructor.

You implement the filter and sort methods like this:

```
public function addTitleFilter(Query $query, $value)
{
    $query->andWhere(['root.title LIKE' => '%'.$value.'%']);
}

public function addTitleSort(Query $query, $order)
{
    $query->order('root.title', $order == '-' ? 'DESC' : 'ASC');
}
```

The main alias will always be changed to root (at least in this version).

The result will be an array ready to be serialized and output in the controller. However, you can also use the results in your views, making this package suitable even outside of the context of a REST API. Keeping the ORM's hydration will give you models, so you can reuse the same repository/table logic for standard applications as well.

```
{
    "data": [
        {
            "id": 1,
            "title": "Title 1"
        },
        {
            "id": 2,
            "title": "Title 2"
        }
    ],
    "meta": {
        "count": 2,
        "pages": 1
    }
}
```

Input for POST, PUT and PATCH operations can be validated directly by the framework.

```
$input = $apiRequest->getBody();
```

## Request Format

```
/games?title=value&sort=-title&pageSize=10&pageNumber=2
```

* Use `sort` with an optional minus (-) sign to inverse the order.
* Use pageSize & pageNumber to control pagination.
* Every other query parameter will be interpreted as a filter.

Remember that sort and filter key names will merely be converted to method names, such as addTitleFilter, and so are independent from your column names in the database schema.

Input for POST, PUT and PATCH operations should be provided using JSON directly in the body of the request.

## Framework Examples

### CakePHP 3.2

This is an example of a controller and a table. Demo project: [afilina/cakeapifoo-demo](https://github.com/afilina/cakeapifoo-demo)

```
use Cake\ORM\TableRegistry;
use Cake\Event\Event;

use ApiFoo\Api\ApiRequest;
use ApiFoo\Adapters\Cake\V3_2\CakeRequest;

class GamesController extends AppController
{
    public function beforeRender(Event $event)
    {
        $this->RequestHandler->renderAs($this, 'json');
        $this->response->type('application/json');
    }

    public function getList()
    {
        $apiRequest = new ApiRequest(new CakeRequest($this->request));

        $gamesTable = TableRegistry::get('Games');
        $response = $gamesTable->getApiList($apiRequest);

        $this->set('response', $response);
        $this->set('_serialize', 'response');
    }
}

use ApiFoo\Api\ApiRepository;
use ApiFoo\Api\ApiRequest;
use ApiFoo\Adapters\Cake\V3_2\CakeOrm;

class GamesTable extends Table
{
    public function getApiList(ApiRequest $apiRequest, $hydrate = false)
    {
        $this->alias('root');
        $query = $this
            ->find('all')
            ->hydrate($hydrate)
            ->select(['root.id', 'root.title'])
        ;

        $apiRepository = new ApiRepository(new CakeOrm($this));
        $results = $apiRepository->getList($query, $apiRequest);

        return $results;
    }
}
```

Saves entities in the CakePHP table class (GamesTable) either using `$item = $this->newEntity($apiRequest->getBody());` or `$item = $this->patchEntity($item, $apiRequest->getBody());`

Validation is handled by the framework automatically. The easiest way to add the rules is to create this method in GamesTable:

```
use Cake\Validation\Validator;

public function validationDefault(Validator $validator)
{
    $validator
        ->requirePresence('title')
        ->notEmpty('title', 'Cannot be empty')
        ->add('title', [
            'length' => [
                'rule' => ['minLength', 2],
                'message' => 'Min 2 characters',
            ]
        ])
    ;
    return $validator;
}
```

## Contributing

I am open to pull requests if you find a better way to do things or add useful features. Documentation contributions are also welcome.

I am planning to implement adapters for Symfony and Laravel in the near future as well as adding support for previous framework versions. I'll probably have to split the adapters into separate repos to avoid your pulling all the framework version dependencies into your projects.

I also want a standardized error array in case of validation errors, perhaps using another adapter.
