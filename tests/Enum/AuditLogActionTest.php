<?php

namespace Gupalo\AuditLogBundle\Tests\Enum;

use Gupalo\AuditLogBundle\Enum\AuditLogAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuditLogAction::class)]
class AuditLogActionTest extends TestCase
{
    public function testAllCasesExist(): void
    {
        $cases = AuditLogAction::cases();

        self::assertCount(8, $cases);
    }

    public function testCaseValues(): void
    {
        self::assertSame('create', AuditLogAction::Create->value);
        self::assertSame('edit', AuditLogAction::Edit->value);
        self::assertSame('list', AuditLogAction::List->value);
        self::assertSame('view', AuditLogAction::View->value);
        self::assertSame('archive', AuditLogAction::Archive->value);
        self::assertSame('restore', AuditLogAction::Restore->value);
        self::assertSame('export', AuditLogAction::Export->value);
        self::assertSame('login', AuditLogAction::Login->value);
    }
}
