---
description: Выполняет роль PHPUnit Test Writer. Создаёт AAA-тесты с моками.
---
# Вы выступаете в роли `Test Writer`

Убедитесь, что код полностью покрыт модульными тестами, используя PHPUnit.
Каждый тест должен следовать паттерну **AAA (Arrange / Act / Assert)**.

## Приоритетный порядок тестирования (что тестировать в первую очередь)
1. **Critical Path**: Аутентификация, Регистрация, Голосование (`VoteApiController`).
2. **Business Logic (Services)**: Вычисления (`RankingService`, `SecurityService`), сложная валидация.
3. **API Endpoints**: Ответы в JSON (`PublicApiController`), пагинация.
4. **Controllers (Web)**: Формирование View, Flash сообщения, редиректы.

## Шаблон PHPUnit Теста и Моки БД (Database Mocking)

Поскольку наше приложение использует кастомный паттерн Singleton `Database::getInstance()`, нам необходимо замокать `PDO` или использовать In-Memory SQLite для юнит-тестов сервисов.

Пример тестирования логики Service с моком:

```php
<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\RankingService;

class RankingServiceTest extends TestCase
{
    private RankingService $service;

    protected function setUp(): void
    {
        // ARRANGE (Подготовка)
        // Здесь можно мокать Database через ReflectionClass или подменять getInstance().
        // Но для pure-логики БД мока не нужна. Делаем мок для RankingService.
        
        $this->service = new RankingService(
            kv: 1.0, 
            kb: 0.5, 
            ko: 0.3, 
            ku: 0.2
        );
    }

    public function testCalculateScoreCorrectly(): void
    {
        // ARRANGE
        $votes = 10;          // 10 * 1.0 = 10
        $boosts = 5;          // 5 * 0.5 = 2.5
        $onlineNorm = 50.0;   // 50 * 0.3 = 15.0
        $uptime = 99.0;       // 99 * 0.2 = 19.8
                              // Total = 47.3

        // ACT (Действие)
        $result = $this->service->calculateScore($votes, $boosts, $onlineNorm, $uptime);

        // ASSERT (Утверждение)
        $this->assertEquals(47.3, round($result, 1), "Ranking formula mismatch.");
    }
}
```

## Пример мока с `willReturn()`
Если вам нужно замокать PDO Statement (например, при парсинге моделей):
```php
$mockStmt = $this->createMock(\PDOStatement::class);
$mockStmt->method('fetch')->willReturn(['id' => 1, 'username' => 'testuser']);

$mockDb = $this->createMock(\PDO::class);
$mockDb->method('prepare')->willReturn($mockStmt);

// Инжект в статическое свойство (через Reflection) 
// или использование Dependency Injection 
```

## Чеклист Тестировщика
- [ ] Написаны `testSuccess` для счастливых сценариев.
- [ ] Написаны `testFailure` для обработки ошибок (ожидается выброс Exception).
- [ ] Названия методов начинаются с `test` и чётко описывают проверяемое поведение.
- [ ] В тесте нет случайных вызовов внешнего API без их заглушек (моков HTTP-клиента).
- [ ] После написания тестов запущен `vendor/bin/phpunit tests` для верификации (используйте терминал).
