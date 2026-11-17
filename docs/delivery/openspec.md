# OpenSpec — рабочий процесс

Используется схема `spec-driven`, настройки находятся в `openspec/config.yaml`.
Адаптеры сгенерированы OpenSpec CLI 1.14.0 для OpenCode, Claude Code и Codex.

## Адаптеры агента
| Инструмент | Файлы | Начало изменения | Реализация |
| --- | --- | --- | --- |
| OpenCode | `.opencode/commands/`, `.opencode/skills/` | `/opsx-propose` | `/opsx-apply` |
| Claude Code | `.claude/commands/`, `.claude/skills/` | `/opsx:propose` | `/opsx:apply` |
| Codex | `.agents/skills/` | `$openspec-propose` | `$openspec-apply-change` |

В Codex desktop skills выбираются через интерфейс. Базовый профиль также
содержит explore, update, sync и archive. Propose подготавливает артефакты;
реализация начинается отдельным вызовом apply после просмотра плана.

Сгенерированные инструкции обновлять через `openspec update`, а проектные
правила хранить в `AGENTS.md` и `openspec/config.yaml`.

## Назначение каталогов
- `docs/product/`: исходные требования с ID, контекст и открытые вопросы.
- `docs/domain/`: общий язык и сквозные инварианты.
- `docs/architecture/`: обзор и принятые архитектурные решения.
- `openspec/specs/`: актуальные спецификации поведения по возможностям продукта.
- `openspec/changes/<change-name>/`: proposal, design, tasks и delta specs конкретного изменения.
- `openspec/changes/archive/`: история завершённых изменений.

Каталоги спецификаций пока пусты: поведение ещё не спроектировано и не реализовано.
Подробные сценарии хранить в OpenSpec, а не копировать в несколько документов.

## Цикл изменения
1. Выбрать небольшой законченный сценарий и ID затронутых требований.
2. Создать change, получить инструкции CLI для артефактов схемы.
3. Описать цель и границы в proposal, поведение в delta specs, решения в design, работу и проверки в tasks.
4. Открытые вопросы разрешить или обозначить как допущения до реализации затронутого поведения.
5. Проверить change командой `openspec validate <change-name> --strict`.
6. Реализовать задачи и выполнить проверки; сохранить результаты с привязкой к требованиям.
7. Архивировать завершённое изменение с обновлением основных specs.

Проверка формата OpenSpec не доказывает корректность приложения.
Готовая спецификация не означает готовую реализацию.

## CLI
```sh
openspec new change <change-name>
openspec status --change <change-name>
openspec instructions proposal --change <change-name>
openspec validate <change-name> --strict
openspec archive <change-name>
```

При работе из AI-инструмента использовать соответствующий адаптер либо CLI.
После генерации может потребоваться перезапуск сессии для обнаружения команд и skills.
