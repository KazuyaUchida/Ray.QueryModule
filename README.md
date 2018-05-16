# Ray.QueryModule

Ray.QueryModule converts SQL string into invokable DB objects.

## Installation

### Composer install

    $ composer require ray/query-module 1.x-dev
 
### Module install

```php
use Ray\Di\AbstractModule;
use Ray\Query\SqlQueryModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new SqlQueryModule($sqlDir));
    }
}
```

### SQL files

$sqlDir/todo_insert.sql

```sql
INSERT INTO todo (id, title) VALUES (:id, :title)
```

$sqlDir/todo_item_by_id.sql 

```sql
SELECT * FROM todo WHERE id = :id
```

## Usage

## Inject callable object

A callable object injected into the constructor. Those object was made in specified sql with `@Named` binding.

```php
class Todo
{
    /**
     * @var callable
     */
    private $todoInsert;
    
    /**
     * @var callable
     */
    private $todoItem;
    
    /**
     * @Named("todoInsert=todo_insert, todoItem=todo_item_by_id")
     */
    public function __construct(
        callable $todoInsert,
        callable $todoItem
    ){
        $this->todoInsert = $todoInsert;
        $this->todoItem = $todoItem;
    }
    
    public function get(string $uuid)
    {
        return ($this->todoItem)(['id' => $uuid]);
    }

    public function create(string $uuid, string $title)
    {
        ($this->todoInsert)([
            'id' => $uuid,
            'title' => $title
        ]);
    }
}
```
## Item or List

You can speciafy expected return value type is eihter `item` or `list` with `ItemInterface` or `ListInterface`. 
`ItemInterface` is handy to specify SQL which return single row.

```php
use Ray\Query\ItemInterface;

/**
 * @Named("todo_item_by_id")
 */
public function __construct(ItemInterface $todo)
{
    $this->todo = $todo;
}
```

```php
use Ray\Query\ListInterface;

/**
 * @Named("todos")
 */
public function __construct(ListInterface $todos)
{
    $this->todos = $tods;
}
```

## Override the method with callable object

Entire method invocation can be override with callable object in specified with `@AliasQuery`.

```php
class Foo
{
    /**
     * @AliasQuery(id="todo_item_by_id")
     */
    public function get(string $id)
    {
    }
}
```

When parameter name is different method arguments and Query object arguments, uri_template style expression can solve it.

```php
class Foo
{
    /**
     * @AliasQuery(id="todo_item_by_id?id={a}", templated=true)
     */
    public function get(string $a)
    {
    }
}
```

Specify `type='item'` when single row result is expected to return.

```
/**
 * @AliasQuery("ticket_item_by_id", type="item")
 */
public function onGet(string $id) : ResourceObject
{
}
```

## Demo

```
php demo/run.php
```

## BEAR.Sunday example

 * [Koriym.Ticketsan](https://github.com/koriym/Koriym.TicketSan/blob/master/src/Resource/App/Ticket.php)

