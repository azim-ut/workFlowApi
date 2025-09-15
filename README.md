# Workflow API (PHP 7.4+)

**Workflow API** — это лёгкий и расширяемый инструмент для описания и исполнения бизнес-логики на основе JSON-конфигов.  
Плагин позволяет вынести алгоритмы в декларативные схемы (например, `workflow.json`), а исполнение делегировать PHP-коду, сохраняя чистоту архитектуры.

---

## Возможности

- Декларативное описание логики в JSON:
    - `payload` — входные данные, неизменные. Содержимое payload доступно только для чтения, payload можно передовать в качестве параметров в commands;
    - `state` — внутренние параметры, в state можно сохранять результаты исполнения commands или передавать в commands в качестве параметров на чтение/запись;
    - `commands` — зарегистрированные команды (методы, функции);
    - `route` — последовательность блоков (ACTION, IF_ELSE, SWITCH, LOG и др.);
- Поддержка переменных `${payload.*}`, `${state.*}`;
- Выполнение кастомных команд, зарегистрированных через `setCommand()`;
- Лёгкая интеграция с сервисами и библиотеками;
- Логирование шагов исполнения.

---

## Установка

```bash
composer require azim-ut/workflow-api
```

## Пример использования
```php
$textService = new TextService();

$config = WorkflowService::builder()
    ->fromJsonFile(__DIR__ . '/conf/workflow.json')
    ->setCommand("getTextLength", [$textService, "getTextLength"])
    ->setCommand("matchPattern", [$textService, "matchAny"])
    ->setCommand("setStatus", [$textService, "setStatus"])
    ->build();

$payload = new stdClass();
$payload->text = "Hello world!";
$payload->patterns = ["ello", "earth"];

$result = WorkflowService::executor()->run($config, $payload);
```

## Пример workflow.json
```json
{
  "state": {"size": 0, "match": false, "status": "OPEN"},
  "commands": [
    "getTextLength",
    "matchPattern",
    "setStatus"
  ],
  "route": [
    {
      "id": 1,
      "type": "COMMAND",
      "command": "getTextLength ${payload.text} welcomeBtn closeBtn",
      "state": "size",
      "next": 2
    },
    {
      "id": 2,
      "type": "IF_ELSE",
      "condition": "${state.size} < 10",
      "yes": 6,
      "no": 3
    },
    {
      "id": 3,
      "type": "COMMAND",
      "command": "matchPattern ${payload.text} ${payload.patterns}",
      "state": "match",
      "next": 4
    },
    {
      "id": 4,
      "type": "IF_ELSE",
      "condition": "${state.match}",
      "yes": 6,
      "no": 7
    },

    {
      "id": 6,
      "type": "STATE",
      "target": "status",
      "val": "CLOSED",
      "next": 7
    },
    {
      "id": 7,
      "type": "COMMAND",
      "command": "setStatus ${payload.text} ${payload.status}",
      "next": 0
    }
  ],
  "log": []
}

```
