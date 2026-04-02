<?php

namespace Gupalo\AuditLogBundle\Tests\EventSubscriber;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Gupalo\AuditLogBundle\Entity\AuditLog;
use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;
use Gupalo\AuditLogBundle\EventSubscriber\AuditLogEventSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[CoversClass(AuditLogEventSubscriber::class)]
class AuditLogEventSubscriberTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private TokenStorageInterface&MockObject $tokenStorage;
    private AuditLogEventSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->subscriber = new AuditLogEventSubscriber($this->em, $this->tokenStorage);
    }

    public function testPostPersistIgnoresNonAuditableEntity(): void
    {
        $entity = new \stdClass();
        $args = $this->createLifecycleEventArgs($entity);

        $this->em->expects(self::never())->method('persist');
        $this->em->expects(self::never())->method('flush');

        $this->subscriber->postPersist($args);
    }

    public function testPostPersistCreatesAuditLog(): void
    {
        $entity = $this->createAuditableEntity(123);
        $args = $this->createLifecycleEventArgs($entity);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getEntityChangeSet')->willReturn([]);
        $this->em->method('getUnitOfWork')->willReturn($uow);

        $this->setupTokenStorage('admin@example.com');

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function (AuditLog $audit) use (&$persisted): void {
            $persisted[] = $audit;
        });
        $this->em->expects(self::once())->method('flush');

        $this->subscriber->postPersist($args);

        self::assertCount(1, $persisted);
        self::assertSame('create', $persisted[0]->getAction());
        self::assertSame(123, $persisted[0]->getEntityId());
    }

    public function testPostPersistCreatesFieldChangeEntries(): void
    {
        $entity = $this->createAuditableEntity(1);
        $args = $this->createLifecycleEventArgs($entity);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getEntityChangeSet')->willReturn([
            'name' => [null, 'John'],
            'email' => [null, 'john@example.com'],
        ]);
        $this->em->method('getUnitOfWork')->willReturn($uow);
        $this->setupTokenStorage('admin@example.com');

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function (AuditLog $audit) use (&$persisted): void {
            $persisted[] = $audit;
        });

        $this->subscriber->postPersist($args);

        // 1 create + 2 field changes
        self::assertCount(3, $persisted);
        self::assertSame('create', $persisted[0]->getAction());
        self::assertSame('edit', $persisted[1]->getAction());
        self::assertSame('name', $persisted[1]->getField());
        self::assertSame('edit', $persisted[2]->getAction());
        self::assertSame('email', $persisted[2]->getField());
    }

    public function testPostUpdateIgnoresNonAuditableEntity(): void
    {
        $entity = new \stdClass();
        $args = $this->createLifecycleEventArgs($entity);

        $this->em->expects(self::never())->method('persist');

        $this->subscriber->postUpdate($args);
    }

    public function testPostUpdateDetectsArchive(): void
    {
        $entity = $this->createAuditableEntity(1);
        $args = $this->createLifecycleEventArgs($entity);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getEntityChangeSet')->willReturn([
            'archivedAt' => [null, new DateTime()],
        ]);
        $this->em->method('getUnitOfWork')->willReturn($uow);
        $this->setupTokenStorage('admin@example.com');

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function (AuditLog $audit) use (&$persisted): void {
            $persisted[] = $audit;
        });

        $this->subscriber->postUpdate($args);

        // 1 archive + 1 field edit
        self::assertGreaterThanOrEqual(1, count($persisted));
        self::assertSame('archive', $persisted[0]->getAction());
    }

    public function testPostUpdateDetectsRestore(): void
    {
        $entity = $this->createAuditableEntity(1);
        $args = $this->createLifecycleEventArgs($entity);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getEntityChangeSet')->willReturn([
            'archivedAt' => [new DateTime(), null],
        ]);
        $this->em->method('getUnitOfWork')->willReturn($uow);
        $this->setupTokenStorage('admin@example.com');

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function (AuditLog $audit) use (&$persisted): void {
            $persisted[] = $audit;
        });

        $this->subscriber->postUpdate($args);

        self::assertGreaterThanOrEqual(1, count($persisted));
        self::assertSame('restore', $persisted[0]->getAction());
    }

    public function testResolveFieldValueDateTime(): void
    {
        $entity = $this->createAuditableEntity(1);
        $args = $this->createLifecycleEventArgs($entity);

        $date = new DateTime('2024-01-15 10:30:00');
        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getEntityChangeSet')->willReturn([
            'updatedAt' => [null, $date],
        ]);
        $this->em->method('getUnitOfWork')->willReturn($uow);
        $this->setupTokenStorage('admin@example.com');

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function (AuditLog $audit) use (&$persisted): void {
            $persisted[] = $audit;
        });

        $this->subscriber->postPersist($args);

        // Find the edit entry for updatedAt
        $editEntries = array_filter($persisted, fn(AuditLog $a) => $a->getField() === 'updatedAt');
        $editEntry = array_values($editEntries)[0];
        self::assertStringContainsString('2024-01-15', $editEntry->getAfterValue());
    }

    public function testResolveFieldValueArray(): void
    {
        $entity = $this->createAuditableEntity(1);
        $args = $this->createLifecycleEventArgs($entity);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getEntityChangeSet')->willReturn([
            'roles' => [[], ['ROLE_ADMIN']],
        ]);
        $this->em->method('getUnitOfWork')->willReturn($uow);
        $this->setupTokenStorage('admin@example.com');

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function (AuditLog $audit) use (&$persisted): void {
            $persisted[] = $audit;
        });

        $this->subscriber->postPersist($args);

        $editEntries = array_filter($persisted, fn(AuditLog $a) => $a->getField() === 'roles');
        $editEntry = array_values($editEntries)[0];
        self::assertSame('["ROLE_ADMIN"]', $editEntry->getAfterValue());
    }

    public function testResolveFieldValueScalar(): void
    {
        $entity = $this->createAuditableEntity(1);
        $args = $this->createLifecycleEventArgs($entity);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getEntityChangeSet')->willReturn([
            'age' => [25, 26],
        ]);
        $this->em->method('getUnitOfWork')->willReturn($uow);
        $this->setupTokenStorage('admin@example.com');

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function (AuditLog $audit) use (&$persisted): void {
            $persisted[] = $audit;
        });

        $this->subscriber->postPersist($args);

        $editEntries = array_filter($persisted, fn(AuditLog $a) => $a->getField() === 'age');
        $editEntry = array_values($editEntries)[0];
        self::assertSame('25', $editEntry->getBeforeValue());
        self::assertSame('26', $editEntry->getAfterValue());
    }

    private function createLifecycleEventArgs(object $entity): LifecycleEventArgs
    {
        $args = $this->createMock(LifecycleEventArgs::class);
        $args->method('getObject')->willReturn($entity);

        return $args;
    }

    private function createAuditableEntity(int $id): AwareAuditLogInterface
    {
        $entity = $this->createMock(AwareAuditLogInterface::class);

        // Add getId() via anonymous class wrapping
        return new class($entity, $id) implements AwareAuditLogInterface {
            public function __construct(
                private readonly AwareAuditLogInterface $inner,
                private readonly int $id,
            ) {
            }

            public function getId(): int
            {
                return $this->id;
            }
        };
    }

    private function setupTokenStorage(string $userIdentifier): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUserIdentifier')->willReturn($userIdentifier);
        $this->tokenStorage->method('getToken')->willReturn($token);
    }
}
