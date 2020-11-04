# models

## Development principles

Before using the framework it is important to understand the development principles of Webbundels so you know why certain things work the way they do. Webbundels develops on an extended [MVC](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture pattern, which will be explained in this chapter.

Instead of putting business logic and retrieving data from databases in controllers Webbundels uses model services, business services and repositories. Every model has a corresponding model service and repository. 
When data needs to be retrieved from the database we ask a model service to retrieve it, wich in his turn uses a repository to actually retrieve it.

When data from the database is requested, the model service is responsible for handling and verifying any data that came with the request (e.g. filter data)  before it goes to the repository. With this approach the logic for retrieving data from the database in a repository can be minimized. This is what we want because the responibility of a repository should only be retrieving the desired data from the database and returning it to the model service. When the data from the database is returned to the model service by the repository, the model service is responsible for handling, verifying and mutating the data from the database before it is shown to the requester.

When controllers need 'easy obtainable' data or want easy tasks to be performed (like deleting or updating a record in the database) the model services can be used directly in the controllers. However, when more complex logic is required, business logic services can be used. Multiple model services can be used in the business services to accomplish more difficult tasks.

## Usage

This chapter is a summary of everything the framework has to offer.

### Model Services

Model services are the objects where all data requests go through. Because not all data requests need to be handled by model services, all methods that are called on the model service that are not present will automatically be redirected to the repository that is set on the class attribute 'repo'.

To create a model service you can use the following command:

```
php artisan wb:make:model-service #Modelname
``` 

This way, lots of default code, like the corresponding repository, extended classes, etc is all automatically set.

### Repositories

Repositories are responsible for retrieving data from the database. A lot of predefined methods are available in the abstract repository. Every repository extends the abstract repository. As long as there are no exotic queries required, most of the data should be obtainable by using these predefined methods. Before we go trough all of them, we first explain the parameters that are used in these methods.

__Array $filters__\
The parameter '$filters' is used for filtering, ordering, grouping and other special query extentions. How this filtering works and which default filtering possibilities are possible is explained in the next chapter 'Models'.

__Array $with__\
The parameter '$with' is used when relations need to be pulled along with the model that is requested. How this can be used is explained in the next chapter 'Models'.

__Int $id__\
This parameter is a default filtering method for getting a model of the id '$id'.

__Array $ids__\
This parameter is a default filtering method for getting models that have an id that is present in the array '$ids'.

__Array $data__\
This parameter is used for updating or inserting data. The data in the array $data should be the data that you want to insert or update. The array should look like this:

```php
[attribute-1 => value, attribute-2 => value]
```

You can find a list of all the predefined methodes below.

__get(array $filters = [], array $with = [])__\
Get all models that comply on the array '$filters' with the relations '$with'.

__first(array $filters = [], array $with = [])__\
Get the first model that complies on the array '$filters' with the relations '$with'.

__getAll(array $with = [])__\
Get all models with the relations '$with'.

__getById($id, array $with = [])__\
Get an model with the id '$id' and with the relations '$with'.

__getByIds($ids, array $with = [])__\
Get all models that have an id that is present in the array '$ids'.

__getByName($name, array $with = [])__\
Get a model where the column name is the same as '$name' and with the relations '$with'.

__pluckIds(array $filters = [])__\
Get all ids of the models that comply on the array $filters.

__pluck(string $column, array $filters = [])__\
Get a collection with values of the column '$column' of the models that comply on the array '$filters'.

__pluckWithKeys(string $column, string $key, array $filters = [])__\
Get a collection with as keys the values of the column '$key' and as values the values of column '$column' of the models that comply on the array '$filters'.

__count(array $filters = [])__\
Get the amount of models that comply on the array '$filters'.

__paginate(array $filters = [], array $with = [])__\
Get all models that comply to the array '$filters' with the relations '$with' paginated, further readings on [pagination can be found here](https://github.com/laravel/laravel).

__increment($column, array $filters = [], $amount = 1)__\
Increment column '$column' by an amount of '$amount'(default: 1) for all models that comply on the array '$filters'.

__decrement($column, array $filters = [], $amount = 1)__\
Decrement column '$column' by an amount of '$amount'(default: 1) for all models that comply on the array '$filters'.

__store(array $data, array $with = [])__\
Store a model with the data '$data', return the model with the relations '$with'. The array should look like this:

```php
[attribute-1 => value, attribute-2 => value]
```

__storeMany(array $data)__\
Store many models with the data '$data', return the model with the relations '$with'. The array should look like this:

```php
[
    [attribute-1 => value, attribute-2 => value], // Model-1
    [attriute-1 => value, attribute-2 => value] // Model-2
]
```

__firstOrStore(array $data, array $delayed_data = [])__\
Get the first model that complies to the filters array '$data', if none exists, store the model with the data '$data' combined with '$delalayed_data'.

__update($id, array $data, array $with = [])__\
Update the model that has id '$id' with the data '$data' and return the model with the relations '$with'.

__updateFiltered($filters, array $data, array $with = [])__\
Update all models that comply to the filters '$filters' with the data '$data' and return all models with the relations '$with'.

__destroy($id, array $with = [], $forceDelete = false)__\
Delete the model that has the id '$id', return the model with the relations '$with'. If forceDelete is true, the model will be permanently deleted from the database.

__destroyMany(array $ids, $forceDelete = false)__\
Delete all models that have an id present in the array '$ids'. If forceDelete is true, the model will be permanently deleted from the database.

__forceDestroy($id, array $with = [])__\
Force delete the model that has the id '$id', return the model with the relations '$with'.

To create a repository you can use the following command:

```
php artisan wb:make:repository #Modelname
``` 

### Models

Models are used how they are always used in the MVC architecture. There are two methods in the abstract model to make filtering and getting relationships with filters easier.

To create a model you can use the following command:

```
php artisan wb:make:model #Modelname
``` 

#### scopeFilter

The first one is the method scopeFilter($query, $filters), this is a [local scope](https://laravel.com/docs/5.8/eloquent#local-scopes) method. This method makes filtering with an array possible and is also the method that is used to filter in the repositories.

##### Filtering by key

There are a few predefined keys to filter on, you can find a list below. In these examples you have to replace 'column' with the column you want to filter on. All examples should be self explanatory.

* ['column_is' => value]
* ['column_is_not' => value]
* ['column_is_in' => [value, value]]
* ['column_is_not_in' => [value, value]]
* ['column_is_greater_than' => value]
* ['column_is_not_greater_than' => value]
* ['column_is_greater_or_equal_than' => value]
* ['column_is_not_greater_or_equal_than' => value]
* ['column_is_less_than' => value]
* ['column_is_not_less_than' => value]
* ['column_is_less_or_equal_than' => value]
* ['column_is_not_less_or_equal_than' => value]
* ['take' => value]
* ['skip' => value]
* ['without_scope' => value]
* ['order_by' => value]
* ['order_by_desc' => value]
* ['order_by' => ['order_by_column', 'order_by_colum', 'order_by_desc_column']]
* ['relation' => $filters_array] <- this works as a [whereHas](https://laravel.com/docs/5.8/eloquent-relationships#querying-relationship-existence)
* ['!relation' => $filters_array] <- this works as a [whereDoesntHave](https://laravel.com/docs/5.8/eloquent-relationships#querying-relationship-absence)

#### Filtering by value

In addition to filtering on keys, there are also two predefined filters on values in an array. These are:
['with_trashed', 'without_scopes']

#### Custom filters

If you need a little bit more power and want to add your own filter possibilities to certain models you can do this by copying the 'scopeFilter' method to the desired model. It's important that you call the 'scopeFilter' method of the parent or you will lose all the power of the default filtering methods. An example of adding an custom filter to get all post where the title contains a certain string can be accomplished by adding this to your 'Post' model:

```php
public function scopeFilter($query, $filters = [])
{
    $query = parent::scopeFilter($query, $filters);
    
    if (array_key_exists('title_contains', $filters) {
        $query = $query->where('title', 'like', '%' . $filters['title_contains'] . '%'); 
    }
    
    return $query;
}
```

With this method you can get all posts where the title contains 'Webbundels' by doing:

```php
$webbundelsPosts = Post::filter(['title_contains' => 'Webbundels'])->get();
```

Or if you want to accomplish the same by using a model service you can do the following:

```php
$webbundelsPosts = App::make('PostService')->get(['title_contains' => 'Webbundels']);
```

### scopeWiths

The second method is 'scopeWith($query, $with)' wich is also an local scope method. This is also the method that is used when a '$withs' parameter is used on one of the repository methods. The method works the same as the normal [with method in models](https://laravel.com/docs/6.0/eloquent-relationships), except, when trying to [eager load a relation with contraints](https://laravel.com/docs/6.0/eloquent-relationships#constraining-eager-loads), you can use an array for the constraint/filters. These work the same as the filters as the 'scopeFilter' method, it uses the same function for the constraints. For instance, when trying to get post id '1' with all comments that are made after '2019-01-01', you can do this:

```php
App::make('PostService')->getById(1, ['comments' => ['created_at_is_greater_than' => '2019-01-01']]);
```

Furthermore, there is also the possibility to count the number of relationsships of an relationship. This is done by using count before the relationship key. For instance, if we want to do the same as the example before but instead of getting the comments after '2019-01-01' we only want the amount of comments, you can do this:

```php
$post = App::make('PostService')->getById(1, ['countComments' => ['created_at_is_greater_than' => '2019-01-01']]);
echo $post->comments_count;
```

Even filtering nested relationships isn't a problem. For instance, if you want to get the same post with the same comments, but also the likes of the comments if the comments are of user id '1337', you can do this:

```php
$post = App::make('PostService')->getById(1, [
    'comments' => ['created_at_is_greater_than' => '2019-01-01']
    'comments.likes' => ['likes' => ['user_id_is' => 1337]]
]);
```

Or if you want specify it further and only want the comments that have likes from user id '1337' and also get thes likes, we can do this:

```php
$post = App::make('PostService')->getById(1, [
    'comments' => [
        'created_at_is_greater_than' => '2019-01-01'
        'likes' => ['user_id_is' => 1337]
    ]
    'comments.likes' => ['likes' => ['user_id_is' => 1337]]
]);
```

### Model-classes

When you want to make a new model, in most of the cases you also want a migration, model service, repository, [resource](https://laravel.com/docs/6.0/eloquent-resources#generating-resources), [resourceCollection](https://laravel.com/docs/6.0/eloquent-resources#resource-collections) and bind the model service to your service container. 
Creating all these files manually can be very time consuming, especially when you have a new project and need to make a lot of them. To create all of this files you can use the following command:

```
php artisan wb:make:model-classes #Modelname
```
