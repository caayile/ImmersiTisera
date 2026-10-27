# Controllers Rules

## Controller Structure

Each controller should:
- Extend `App\Http\Controllers\Controller`
- Use dependency injection for services in constructor
- Use Form Requests for validation
- Return appropriate HTTP responses
- Use policies for authorization

## RESTful Convention

Follow standard REST patterns:

| Method | Route | Action | Description |
|--------|-------|--------|-------------|
| GET | /resource | index | List resources |
| GET | /resource/create | create | Show creation form |
| POST | /resource | store | Create new resource |
| GET | /resource/{id} | show | Show single resource |
| GET | /resource/{id}/edit | edit | Show edit form |
| PUT/PATCH | /resource/{id} | update | Update resource |
| DELETE | /resource/{id} | destroy | Delete resource |

## Authorization

Use policies via `$this->authorize()` in controller methods:

```php
public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);
    // ...
}
```

Or use middleware for role-based access:
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // admin routes
});
```

## Validation

Use Form Request classes:
```bash
php artisan make:request StorePostRequest
```

In controller:
```php
public function store(StorePostRequest $request)
{
    $validated = $request->validated();
    // ...
}
```

## Response Format

### Success Responses
```php
return response()->json([
    'data' => $resource,
    'message' => 'Success message'
], 201); // Use proper status codes
```

### Error Responses
```php
return response()->json([
    'message' => 'Error description',
    'errors' => $errors // optional
], 422);
```

## API Resources

Use Eloquent API Resources for structured responses:
```php
use App\Http\Resources\PostResource;

return new PostResource($post);
// or
return PostResource::collection($posts);
```

## Controller Naming

- Use singular for single resource controllers: `PostController`
- Use plural for collection operations: `PostsController` (rare)
- Use descriptive names: `AdminPostController`, `ApiPostController`

## Best Practices

1. Keep controllers thin - move business logic to services
2. Use route model binding: `Post $post` instead of `$id`
3. Handle exceptions gracefully with try-catch
4. Use `abort_if()` for authorization checks
5. Return 204 No Content for successful deletions
6. Use `$request->validated()` for safe data access
7. Use `with()` for eager loading in index/show methods
8. Cache expensive queries when appropriate