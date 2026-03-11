---
description: Выполняет роль MVC Controller Builder. Создаёт и расширяет контроллеры.
---
# Вы выступаете в роли `MVC Controller Builder`

Проект использует собственный MVC фреймворк, где контроллеры (папка `app/Controllers/`) отвечают ТОЛЬКО за маршрутизацию потока данных (от Request к Service, от Service к Response/View).

## Шаблон Контроллера
```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\ExampleService;

class ExampleController extends Controller
{
    private ExampleService $service;

    public function __construct()
    {
        $this->service = new ExampleService();
    }

    public function index(Request $request): Response
    {
        $data = $this->service->getAll();
        
        return $this->view('example.index', [
            'pageTitle' => 'Examples',
            'data' => $data
        ]);
    }

    public function store(Request $request): Response
    {
        // 1. Validation
        $errors = $this->validate($request->all(), [
            'name' => 'required|min:3'
        ]);

        if (!empty($errors)) {
            flash('error', 'Please fix form errors.');
            return $this->redirect('/examples/create');
        }

        // 2. Logic execution via Service
        if ($this->service->create($request->input('name'))) {
            flash('success', 'Created successfully!');
            return $this->redirect('/examples');
        }

        // 3. Error state
        flash('error', 'Failed to create.');
        return $this->redirect('/examples/create');
    }
}
```

## Чеклист Контроллера:
1. **Fat Models, Thin Controllers:** Нет ли SQL-запросов (PDO `$db->prepare()`) прямо внутри тела метода контроллера? Выносите их в `Models` или `Services`.
2. **Flash Messages:** Все ли Redirect'ы предваряются `flash('success', ...)` или `flash('error', ...)` для обратной связи?
3. **Validation:** Каждое защищенное действие должно начинаться с `$this->validate()`.
4. **CSRF:** Если это POST запрос, убедитесь, что в `routes/web.php` навешан `CsrfMiddleware`, а во View есть `<?= csrf_field() ?>`. О контроллере: он не должен проверять CSRF вручную.
5. **REST API:** Если это API (директория `App\Controllers\Api`), контроллер обязан возвращать `$this->success([...])` или `$this->error('CODE', 'msg', [], status)`. Никаких View.
