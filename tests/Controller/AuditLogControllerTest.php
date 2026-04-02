<?php

namespace Gupalo\AuditLogBundle\Tests\Controller;

use Gupalo\AuditLogBundle\Controller\AuditLogController;
use Gupalo\AuditLogBundle\Entity\AuditLog;
use Gupalo\AuditLogBundle\Repository\AuditLogRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;

#[CoversClass(AuditLogController::class)]
class AuditLogControllerTest extends TestCase
{
    public function testListsRendersTemplate(): void
    {
        $items = [new AuditLog(), new AuditLog()];

        $repository = $this->createMock(AuditLogRepository::class);
        $repository->method('findAll')->willReturn($items);

        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with('@AuditLog/lists.html.twig', ['items' => $items])
            ->willReturn('<html></html>');

        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturnMap([
            ['twig', true],
        ]);
        $container->method('get')->willReturnMap([
            ['twig', $twig],
        ]);

        $controller = new AuditLogController($repository);
        $controller->setContainer($container);

        $response = $controller->lists();

        self::assertSame(200, $response->getStatusCode());
    }
}
