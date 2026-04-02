<?php

namespace Gupalo\AuditLogBundle\Tests\EventSubscriber;

use Gupalo\AuditLogBundle\Entity\AuditLog;
use Gupalo\AuditLogBundle\Entity\AwareAuditLogInterface;
use Gupalo\AuditLogBundle\Enum\AuditLogAction;
use Gupalo\AuditLogBundle\EventSubscriber\BaseEventSubscribe;
use Gupalo\AuditLogBundle\Repository\AuditLogRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(BaseEventSubscribe::class)]
class BaseEventSubscribeTest extends TestCase
{
    private AuditLogRepository&MockObject $repository;
    private TokenStorageInterface&MockObject $tokenStorage;
    private RequestStack&MockObject $requestStack;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuditLogRepository::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
    }

    public function testSaveLogCreatesAuditEntry(): void
    {
        $subscriber = $this->createConcreteSubscriber(AuditLogAction::Create);
        $entity = $this->createAuditableEntity(42);
        $this->setupTokenStorage('admin@example.com');
        $this->setupRequest('10.0.0.1');

        $this->repository->expects(self::once())->method('add')
            ->with(self::callback(function (AuditLog $audit): bool {
                return $audit->getAction() === 'create'
                    && $audit->getEntityId() === 42
                    && $audit->getUser() === 'admin@example.com'
                    && $audit->getIp() === '10.0.0.1';
            }), true);

        $subscriber->callSaveLog($entity);
    }

    public function testSaveLogSkipsPrometheusUser(): void
    {
        $subscriber = $this->createConcreteSubscriber(AuditLogAction::View);
        $this->setupTokenStorage('prometheus');
        $this->setupRequest('10.0.0.1');

        $this->repository->expects(self::never())->method('add');

        $subscriber->callSaveLog();
    }

    public function testSaveLogResolvesUserFromToken(): void
    {
        $subscriber = $this->createConcreteSubscriber(AuditLogAction::View);

        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('tokenuser@example.com');

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->setupRequest(null);

        $this->repository->expects(self::once())->method('add')
            ->with(self::callback(fn(AuditLog $a): bool => $a->getUser() === 'tokenuser@example.com'), true);

        $subscriber->callSaveLog();
    }

    public function testSaveLogUsesPassedUser(): void
    {
        $subscriber = $this->createConcreteSubscriber(AuditLogAction::Login);

        $passedUser = $this->createMock(UserInterface::class);
        $passedUser->method('getUserIdentifier')->willReturn('passed@example.com');

        $this->tokenStorage->method('getToken')->willReturn(null);
        $this->setupRequest(null);

        $this->repository->expects(self::once())->method('add')
            ->with(self::callback(fn(AuditLog $a): bool => $a->getUser() === 'passed@example.com'), true);

        $subscriber->callSaveLog(null, $passedUser);
    }

    public function testSaveLogHandlesNullEntity(): void
    {
        $subscriber = $this->createConcreteSubscriber(AuditLogAction::Login);
        $this->setupTokenStorage('user@example.com');
        $this->setupRequest(null);

        $this->repository->expects(self::once())->method('add')
            ->with(self::callback(fn(AuditLog $a): bool => $a->getEntity() === null && $a->getEntityId() === null), true);

        $subscriber->callSaveLog();
    }

    public function testSaveLogGetsClientIp(): void
    {
        $subscriber = $this->createConcreteSubscriber(AuditLogAction::View);
        $this->setupTokenStorage('user@example.com');
        $this->setupRequest('192.168.1.100');

        $this->repository->expects(self::once())->method('add')
            ->with(self::callback(fn(AuditLog $a): bool => $a->getIp() === '192.168.1.100'), true);

        $entity = $this->createAuditableEntity(1);
        $subscriber->callSaveLog($entity);
    }

    private function createConcreteSubscriber(AuditLogAction $action): object
    {
        $repo = $this->repository;
        $ts = $this->tokenStorage;
        $rs = $this->requestStack;

        return new class($repo, $ts, $rs, $action) extends BaseEventSubscribe {
            public function __construct(
                AuditLogRepository $auditLogRepository,
                TokenStorageInterface $tokenStorage,
                RequestStack $requestStack,
                ?AuditLogAction $action,
            ) {
                parent::__construct($auditLogRepository, $tokenStorage, $requestStack);
                $this->action = $action;
            }

            public function callSaveLog(?AwareAuditLogInterface $entity = null, ?UserInterface $user = null): void
            {
                $this->saveLog($entity, $user);
            }
        };
    }

    private function createAuditableEntity(int $id): AwareAuditLogInterface
    {
        return new class($id) implements AwareAuditLogInterface {
            public function __construct(private readonly int $id)
            {
            }

            public function getId(): int
            {
                return $this->id;
            }
        };
    }

    private function setupTokenStorage(string $userIdentifier): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn($userIdentifier);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
    }

    private function setupRequest(?string $clientIp): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getClientIp')->willReturn($clientIp);
        $this->requestStack->method('getCurrentRequest')->willReturn($request);
    }
}
