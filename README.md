# Yii2 router extention
Get & set Module, Controller, Action from DB (slugs table).

1. Frontend
- Option 1: example.com/slug-url => example.com/controller/action
- Option 2: example.com/controller/action (origin router)

2. Module (backend | api | ...)
- Option 1: example.com/slug-url => example.com/module/controller/action
- Option 2: example.com/module/slug-url => example.com/module/controller/action
- Option 3: example.com/module/controller/action (origin router)
- Option 4: example.com/module/version/controller/action (api module)

## Slug table struct
- url: varchar
- router: varchar
- add more everithing if you want (ex: controller, action, module,...)

## continue...