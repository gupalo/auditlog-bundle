<?php

namespace Gupalo\AuditLogBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Gupalo\AuditLogBundle\Entity\AuditLog;
use Gupalo\AuditLogBundle\Repository\AuditLogRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuditLogRepository::class)]
class AuditLogRepositoryTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private AuditLogRepository $repository;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);

        $classMetadata = new ClassMetadata(AuditLog::class);
        $this->em->method('getClassMetadata')->willReturn($classMetadata);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->em);

        $this->repository = new AuditLogRepository($registry);
    }

    public function testAddWithoutFlush(): void
    {
        $entity = new AuditLog();

        $this->em->expects(self::once())->method('persist')->with($entity);
        $this->em->expects(self::never())->method('flush');

        $this->repository->add($entity);
    }

    public function testAddWithFlush(): void
    {
        $entity = new AuditLog();

        $this->em->expects(self::once())->method('persist')->with($entity);
        $this->em->expects(self::once())->method('flush');

        $this->repository->add($entity, true);
    }

    public function testRemoveWithoutFlush(): void
    {
        $entity = new AuditLog();

        $this->em->expects(self::once())->method('remove')->with($entity);
        $this->em->expects(self::never())->method('flush');

        $this->repository->remove($entity);
    }

    public function testRemoveWithFlush(): void
    {
        $entity = new AuditLog();

        $this->em->expects(self::once())->method('remove')->with($entity);
        $this->em->expects(self::once())->method('flush');

        $this->repository->remove($entity, true);
    }

    public function testUpdate(): void
    {
        $this->em->expects(self::once())->method('flush');

        $this->repository->update();
    }
}
