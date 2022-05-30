AuditLog Bundle
===================

## Install

```bash
composer require gupalo/auditlog-bundle
```

Add to `config/bundles.php`

```php
Gupalo\AuditLogBundle\AuditLogBundle::class => ['all' => true]
```

Add to `config/packages/doctrine.yaml`
```yaml
mappings:
    AuditLogBundle:
        type: attribute
```

Add to config/routes/annotations.yaml

```yaml
auditLog:
    resource: '@AuditLogBundle/Resources/config/routes.yaml'
```

## Execute

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
php bin/console assets:install
```

Usage

Audit entity must implements 
```php
Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface
```

For log audit

List:
```php
$this->dispatcher->dispatch(new ListEvent(new AwareAuditLogInterface()));
```
View 
```php
$this->dispatcher->dispatch(new ViewEvent($entity));
```
Create
```php
$this->dispatcher->dispatch(new CreateEvent($entity));
```
Restore
```php
$this->dispatcher->dispatch(new RestoreEvent($entity));
```
Archive
```php
$this->dispatcher->dispatch(new ArchiveEvent($entity));
```
Export
```php
$this->dispatcher->dispatch(new ExportEvent($entity));
```


